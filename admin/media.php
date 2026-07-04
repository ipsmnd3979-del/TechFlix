<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

// Check admin access
if (!$isLoggedIn || ($userData['role'] !== 'admin' && $userData['role'] !== 'superadmin')) {
    header("Location: ../home.php");
    exit();
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['media_files'])) {
    $uploaded_files = [];
    $errors = [];
    
    foreach ($_FILES['media_files']['name'] as $key => $name) {
        if ($_FILES['media_files']['error'][$key] === 0) {
            $file_type = $_FILES['media_files']['type'][$key];
            $file_size = $_FILES['media_files']['size'][$key];
            $file_tmp = $_FILES['media_files']['tmp_name'][$key];
            
            // Determine upload directory based on file type
            if (strpos($file_type, 'image/') === 0) {
                $upload_dir = '../assets/uploads/images/';
            } elseif (strpos($file_type, 'video/') === 0) {
                $upload_dir = '../assets/uploads/videos/';
            } else {
                $upload_dir = '../assets/uploads/other/';
            }
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($name, PATHINFO_EXTENSION);
            $new_filename = 'media_' . time() . '_' . $key . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $uploaded_files[] = [
                    'name' => $name,
                    'path' => $upload_path,
                    'type' => $file_type,
                    'size' => $file_size
                ];
                
                // Log file in database
                $stmt = $conn->prepare("INSERT INTO media_files (filename, filepath, filetype, filesize, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $name, $upload_path, $file_type, $file_size, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
            } else {
                $errors[] = "Failed to upload: " . $name;
            }
        }
    }
    
    if (!empty($uploaded_files)) {
        $success_message = count($uploaded_files) . " file(s) uploaded successfully!";
    }
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
    }
}

// Handle file deletion
if (isset($_POST['delete_file'])) {
    $file_id = intval($_POST['file_id']);
    
    // Get file path first
    $stmt = $conn->prepare("SELECT filepath FROM media_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();
    
    if ($file && file_exists($file['filepath'])) {
        unlink($file['filepath']);
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM media_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    
    if ($stmt->execute()) {
        $success_message = "File deleted successfully!";
    } else {
        $error_message = "Failed to delete file from database.";
    }
    $stmt->close();
}

// Get media files with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$media_query = "SELECT mf.*, u.username as uploaded_by_name 
                FROM media_files mf 
                LEFT JOIN users u ON mf.uploaded_by = u.id 
                ORDER BY mf.uploaded_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($media_query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$media_result = $stmt->get_result();

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM media_files");
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - TechFlix</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
     <style>
/* Media Page Specific Styles */
.media-item {
    transition: all 0.3s ease;
    position: relative;
}

.media-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    border-color: rgba(138, 43, 226, 0.5);
}

#uploadArea:hover {
    border-color: var(--primary);
    background: rgba(138, 43, 226, 0.05);
}

.pagination-btn {
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.05);
    color: #ddd;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.pagination-btn:hover {
    background: rgba(138, 43, 226, 0.2);
    border-color: var(--primary);
    color: white;
}

.pagination-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* File type indicators */
.media-preview .fa-video {
    color: #ff6b6b !important;
}

.media-preview .fa-file {
    color: #74b9ff !important;
}

.media-preview .fa-image {
    color: #55efc4 !important;
}

/* Upload area animations */
.upload-area.dragover {
    border-color: var(--primary) !important;
    background: rgba(138, 43, 226, 0.1) !important;
    transform: scale(1.02);
}

/* Media grid animations */
.media-grid {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* File list styles */
.file-list-item {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}

/* Button enhancements */
.btn {
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.btn-primary {
    background: linear-gradient(135deg, #8a2be2, #6a11cb);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #7b1fa2, #5e0fb3);
}

.btn-outline {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ddd;
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
}

/* Success and error states */
.success-message, .error-message {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Loading state */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Responsive design */
@media (max-width: 1200px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .upload-section,
    .media-grid-section {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .admin-header {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .upload-area {
        padding: 30px 20px;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
    
    .pagination-btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .media-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .upload-options {
        flex-direction: column;
        align-items: stretch;
    }
    
    .upload-options .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Dark theme enhancements */
.media-info h4 {
    transition: color 0.3s ease;
}

.media-item:hover .media-info h4 {
    color: var(--primary);
}

/* File type badges */
.file-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}

/* Progress bar for uploads */
.upload-progress {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 10px;
}

.upload-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #8a2be2, #6a11cb);
    transition: width 0.3s ease;
    width: 0%;
}

/* Empty state styling */
.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #bbb;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: #ddd;
    margin-bottom: 10px;
}

/* Card hover effects */
.admin-header, .upload-section, .media-grid-section {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.admin-header:hover, .upload-section:hover, .media-grid-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}
</style>
<div class="page-content">
    <div class="admin-header" style="background: var(--card-bg); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
        <h1 style="margin: 0; background: linear-gradient(to right, #fff, #d9e3f0); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-photo-video"></i> Media Library
        </h1>
        <p style="color: #bbb; margin: 10px 0 0;">Upload and manage images, videos, and other media files</p>
    </div>

    <!-- Upload Section -->
    <div class="upload-section" style="background: var(--card-bg); padding: 30px; border-radius: 15px; margin-bottom: 40px;">
        <h3 style="margin-bottom: 25px; color: var(--primary);">
            <i class="fas fa-upload"></i> Upload Media Files
        </h3>
        
        <?php if (isset($success_message)): ?>
            <div class="success-message" style="background: rgba(46, 213, 115, 0.1); color: #2ed573; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(46, 213, 115, 0.3);">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message" style="background: rgba(255, 71, 87, 0.1); color: #ff4757; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255, 71, 87, 0.3);">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
            <div class="upload-area" id="uploadArea" style="border: 2px dashed rgba(138, 43, 226, 0.3); border-radius: 10px; padding: 40px; text-align: center; margin-bottom: 20px; transition: all 0.3s; cursor: pointer;">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: rgba(138, 43, 226, 0.5); margin-bottom: 15px;"></i>
                <h4 style="color: #ddd; margin-bottom: 10px;">Drag & Drop Files Here</h4>
                <p style="color: #bbb; margin-bottom: 20px;">or click to select files</p>
                <input type="file" name="media_files[]" multiple accept="image/*,video/*" style="display: none;" id="fileInput">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('fileInput').click()" style="padding: 10px 25px;">
                    <i class="fas fa-folder-open"></i> Choose Files
                </button>
            </div>
            
            <div id="fileList" style="margin-bottom: 20px;"></div>
            
            <div class="upload-options" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                    <i class="fas fa-upload"></i> Upload Files
                </button>
                <div style="color: #bbb; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Supported: Images (JPEG, PNG, GIF), Videos (MP4, MOV, AVI)
                </div>
            </div>
        </form>
    </div>

    <!-- Media Grid -->
    <div class="media-grid-section" style="background: var(--card-bg); padding: 30px; border-radius: 15px;">
        <h3 style="margin-bottom: 25px; color: var(--primary); display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-images"></i> Media Library (<?php echo $total_items; ?> files)</span>
        </h3>
        
        <?php if ($media_result->num_rows > 0): ?>
            <div class="media-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <?php while($file = $media_result->fetch_assoc()): ?>
                <div class="media-item" style="background: rgba(255, 255, 255, 0.05); border-radius: 10px; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="media-preview" style="height: 150px; overflow: hidden; background: #1a1a2e; display: flex; align-items: center; justify-content: center;">
                        <?php if (strpos($file['filetype'], 'image/') === 0): ?>
                            <img src="<?php echo $file['filepath']; ?>" alt="<?php echo $file['filename']; ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                        <?php elseif (strpos($file['filetype'], 'video/') === 0): ?>
                            <i class="fas fa-video" style="font-size: 3rem; color: rgba(255, 255, 255, 0.3);"></i>
                        <?php else: ?>
                            <i class="fas fa-file" style="font-size: 3rem; color: rgba(255, 255, 255, 0.3);"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="media-info" style="padding: 15px;">
                        <h4 style="margin: 0 0 8px 0; color: white; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo $file['filename']; ?>
                        </h4>
                        
                        <div style="color: #bbb; font-size: 0.8rem; margin-bottom: 10px;">
                            <div><?php echo format_file_size($file['filesize']); ?></div>
                            <div><?php echo date('M j, Y', strtotime($file['uploaded_at'])); ?></div>
                        </div>
                        
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; flex: 1;" onclick="copyFilePath('<?php echo $file['filepath']; ?>')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                            <form method="POST" action="" style="display: inline; flex: 1;">
                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                <button type="submit" name="delete_file" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; background: rgba(255, 71, 87, 0.1); border-color: #ff4757; color: #ff4757; width: 100%;" onclick="return confirm('Are you sure you want to delete this file?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; gap: 10px;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">‹ Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-btn active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-btn"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Next ›</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="text-align: center; padding: 60px; color: #bbb;">
                <i class="fas fa-photo-video" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3>No Media Files</h3>
                <p>Upload your first media file using the upload form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
// Enhanced drag and drop functionality
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
const uploadForm = document.getElementById('uploadForm');

// Add these variables for progress tracking
let uploadInProgress = false;

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    handleFiles(files);
});

uploadArea.addEventListener('click', () => {
    if (!uploadInProgress) {
        fileInput.click();
    }
});

fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
});

// Prevent form submission during upload
uploadForm.addEventListener('submit', (e) => {
    if (uploadInProgress) {
        e.preventDefault();
        alert('Please wait for current upload to complete.');
        return;
    }
    
    const files = fileInput.files;
    if (files.length === 0) {
        e.preventDefault();
        alert('Please select at least one file to upload.');
        return;
    }
    
    // Show loading state
    uploadInProgress = true;
    const submitBtn = uploadForm.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    submitBtn.disabled = true;
});

function handleFiles(files) {
    fileList.innerHTML = '';
    
    if (files.length > 0) {
        const fileListHTML = document.createElement('div');
        fileListHTML.innerHTML = `
            <h4 style="color: #ddd; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-alt"></i> Selected Files (${files.length})
            </h4>
        `;
        
        const fileListContainer = document.createElement('div');
        fileListContainer.style.display = 'flex';
        fileListContainer.style.flexDirection = 'column';
        fileListContainer.style.gap = '10px';
        
        let totalSize = 0;
        
        for (let file of files) {
            totalSize += file.size;
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-list-item';
            fileItem.style.display = 'flex';
            fileItem.style.justifyContent = 'space-between';
            fileItem.style.alignItems = 'center';
            fileItem.style.padding = '12px 15px';
            fileItem.style.background = 'rgba(255, 255, 255, 0.05)';
            fileItem.style.borderRadius = '8px';
            fileItem.style.color = '#ddd';
            fileItem.style.fontSize = '0.9rem';
            fileItem.style.borderLeft = '4px solid var(--primary)';
            
            const fileIcon = file.type.startsWith('image/') ? 'fa-image' : 
                           file.type.startsWith('video/') ? 'fa-video' : 'fa-file';
            
            fileItem.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas ${fileIcon}" style="color: var(--primary);"></i>
                    <span style="font-weight: 500;">${file.name}</span>
                </div>
                <span style="color: #bbb; font-size: 0.8rem; background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px;">
                    ${formatFileSize(file.size)}
                </span>
            `;
            
            fileListContainer.appendChild(fileItem);
        }
        
        // Add total size
        const totalSizeElement = document.createElement('div');
        totalSizeElement.style.marginTop = '15px';
        totalSizeElement.style.padding = '10px 15px';
        totalSizeElement.style.background = 'rgba(138, 43, 226, 0.1)';
        totalSizeElement.style.borderRadius = '8px';
        totalSizeElement.style.color = '#ddd';
        totalSizeElement.style.fontSize = '0.9rem';
        totalSizeElement.style.textAlign = 'center';
        totalSizeElement.innerHTML = `
            <strong>Total:</strong> ${files.length} files, ${formatFileSize(totalSize)}
        `;
        
        fileListContainer.appendChild(totalSizeElement);
        fileListHTML.appendChild(fileListContainer);
        fileList.appendChild(fileListHTML);
    }
}

// function formatFileSize(bytes) {
//     if (bytes === 0) return '0 Bytes';
//     const k = 1024;
//     const sizes = ['Bytes', 'KB', 'MB', 'GB'];
//     const i = Math.floor(Math.log(bytes) / Math.log(k));
//     return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
// }

function copyFilePath(filePath) {
    navigator.clipboard.writeText(filePath).then(() => {
        // Show success feedback
        showNotification('File path copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy file path: ', err);
        showNotification('Failed to copy file path', 'error');
    });
}

// Enhanced notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
    `;
    
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #00b894, #00a085)';
    } else if (type === 'error') {
        notification.style.background = 'linear-gradient(135deg, #ff7675, #e17055)';
    } else {
        notification.style.background = 'linear-gradient(135deg, #74b9ff, #0984e3)';
    }
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(notificationStyles);
</script>
<style>
.media-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    border-color: rgba(138, 43, 226, 0.5);
}

#uploadArea:hover {
    border-color: var(--primary);
    background: rgba(138, 43, 226, 0.05);
}

@media (max-width: 768px) {
    .media-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .upload-section {
        padding: 20px;
    }
    
    .media-grid-section {
        padding: 20px;
    }
}
</style>

<?php

?>

<?php require_once '../includes/footer.php'; ?>