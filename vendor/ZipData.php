<?php

define('DS', DIRECTORY_SEPARATOR);

class ZipData
{
    //$source = <absolute pathname to directory/file to be zipped> . DIRECTORY_SEPARATOR;
    //$fileName = "myfiles.zip";
    //$destination = <absolute pathname to destination directory> . DIRECTORY_SEPARATOR . $fileName;

    public static function zip_files( $source, $destination ) {
        $zip = new ZipArchive();
        $result = $zip->open($destination, ZIPARCHIVE::CREATE);
        //$cesar=array();
        if($result === true) {
            $source = wp_normalize_path(realpath($source));
            if(is_dir($source)) {
                $iterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
                foreach($files as $file) {
                    //$cesar[] = $file;
                    $file = wp_normalize_path(realpath($file));
                    if(is_dir($file)) {
                        //$zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
                        $zip->addEmptyDir(str_replace($source.'/', '', $file));
                    } elseif(is_file($file)) {
                        $zip->addFile($file,str_replace($source.'/', '', $file));
                    }
                }
            } elseif(is_file($source)) {
                $zip->addFile($source, basename($source));
            }
        }
        else {
            return $result;
        }
        return $zip->close();
    }
}