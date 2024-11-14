<?php
namespace Adithyan\TransferMedia\Setup\Script;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\Io\File;

class ImageCopy
{
    protected $filesystem;
    protected $directoryList;
    protected $fileIo;

    public function __construct(
        Filesystem $filesystem,
        DirectoryList $directoryList,
        File $fileIo
    ) {
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
    }

    public function copyImages()
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/pre-order/';
        $sourceDir = __DIR__ . '/../../../media/';
        
        if (!$this->fileIo->isDirectory($mediaDir)) {
            $this->fileIo->mkdir($mediaDir, 0777, true); // Create the destination directory if it doesn't exist
        }

        $this->copyDirectory($sourceDir, $mediaDir);
    }

    private function copyDirectory($sourceDir, $destDir)
    {
        if (is_dir($sourceDir)) {
            // Open the source directory
            $dir = opendir($sourceDir);
            while (($file = readdir($dir)) !== false) {
                // Skip the special directories "." and ".."
                if ($file != "." && $file != "..") {
                    $sourceFile = $sourceDir . '/' . $file;
                    $destFile = $destDir . '/' . $file;
                    if (is_dir($sourceFile)) {
                        // Recursively copy subdirectories
                        $this->copyDirectory($sourceFile, $destFile);
                    } else {
                        // Copy files
                        $this->fileIo->cp($sourceFile, $destFile);
                    }
                }
            }
            closedir($dir);
        }
    }
}
