<?php

namespace App\Http\Helper;

use File;

class UploadHelper
{

    const UPLOAD_FOLDER = 'uploads/';
    const PAGE_IMAGE = 'pageImage';
    const PAGE_SETTING = 'pageSetting';
    const POST_IMAGE = 'postImage';

    /**
     * @param  string $file
     * @param  string $fileType
     * @return string $fileName
     */
    public static function uploadFiles($file, $fileType, $deleteOldFile = false, $oldFile = false)
    {
        $filePath = self::fileStoragePath($fileType);
        $fileName = '';
        if ($file) {
            if ($deleteOldFile) {
                self::deleteFile($oldFile, $fileType);
            }
            $extension = $file->getClientOriginalExtension(); // getting image extension
            $fileName = md5(date('Y-m-d H:i:s:u')) . rand(11111, 99999) . '.' . $extension; // rename image
            $file->move($filePath, $fileName);
            return $fileName;
        }
        return $fileName;
    }

    /**
     * @param $file string
     * @param $fileType string
     */
    public static function deleteFile($file, $fileType)
    {
        $filePath = self::fileStoragePath($fileType);
        File::delete($filePath . $file);
    }

    /**
     * @param  string $fileType
     * @return string $filePath
     */
    private static function fileStoragePath($fileType)
    {
        $filePath = '';
        switch ($fileType) {
            case self::PAGE_IMAGE:
                $filePath = self::UPLOAD_FOLDER . 'pages-image/';
                break;
            case self::PAGE_SETTING:
                $filePath = self::UPLOAD_FOLDER . 'settings/';
                break;
            case self::POST_IMAGE:
                $filePath = self::UPLOAD_FOLDER . 'posts-image/';
        }
        return $filePath;
    }
}
