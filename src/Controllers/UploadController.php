<?php
require_once __DIR__ . '/../Models/UploadModel.php';
require_once __DIR__ . '/../Models/CourseModel.php';

class UploadController
{
    public function getCourses(): array
    {
        return UploadModel::getAllCourses();
    }

    public function processUpload(array $file, int $userId, string $title, string $description, string $course, string $customCourse = '', ?int $groupId = null): array
    {
        if ($title === '' || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Titel und Datei sind erforderlich'];
        }
        $allowed = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowed, true)) {
            return ['success' => false, 'message' => 'Dateityp nicht erlaubt'];
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Datei zu groß'];
        }
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'message' => 'Upload-Verzeichnis konnte nicht erstellt werden'];
        }
        $originalName = basename($file['name']);
        $safeName     = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
        $ext          = pathinfo($safeName, PATHINFO_EXTENSION);
        $baseName     = uniqid('up_', true);
        $storedName   = $baseName . '.' . $ext;
        $targetPath   = $uploadDir . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'message' => 'Konnte Datei nicht speichern'];
        }
        if ($course === '__custom__') {
            $courseName = $customCourse;
        } else {
            $courseName = $course;
        }
        try {
            $courseId   = CourseModel::getCourseIdByName($courseName);
            $materialId = CourseModel::getOrCreateMaterial($courseId, $title, $description);
            $uploadId   = UploadModel::uploadFile($storedName, $materialId, $userId, $groupId);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Fehler beim Speichern'];
        }
        return ['success' => true, 'upload_id' => $uploadId];
    }
}
