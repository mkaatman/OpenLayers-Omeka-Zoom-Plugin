<?php
/**
 * Class Name: zoomify
 *
 * @author: Justin Henry [http://greengaloshes.cc]
 * Cleanup for Omeka by Daniel Berthereau (daniel.github@berthereau.net)
 *
 * Purpose: This class contains methods to support the use of the
 * ZoomifyFileProcessor class.  The ZoomifyFileProcessor class is a port of the
 * ZoomifyImage python script to a PHP class.  The original python script was
 * written by Adam Smith, and was ported to PHP (in the form of
 * ZoomifyFileProcessor) by Wes Wright.
 *
 * Both tools do the about same thing - that is, they convert images into a
 * format that can be used by the "zoomify" image viewer.
 *
 * This class provides an interface for performing "batch" conversions using the
 * ZoomifyFileProcessor class. It also provides methods for inspecting resulting
 * processed images.
 */

class zoomify
{
    public $_debug = false;
    public $fileMode = '0644';
    public $dirMode = '0755';
    public $fileGroup = 'www-data';

    /**
     * Constructor
     * Initialize process, set class vars
     *
     * @return void
     */
    function zoomify($imagepath)
    {
        define('IMAGEPATH', $imagepath);
    }

    /**
     * Prints list of html links to a zoomified image.
     *
     * @param string @dir
     *   path to a directory.
     *
     * @return string|boolean
     */
    function listZoomifiedImages($dir)
    {
        if ($dh = @opendir($dir)) {
            while (false !== ($filename = readdir($dh))) {
                if (($filename != '.')
                        && ($filename != '..')
                        && (is_dir($dir . $filename . DIRECTORY_SEPARATOR))
                    ) {
                    echo '<a href="viewer.php?file=' . $filename . '&path="' . $dir . '">' . $filename . '</a><br />' . PHP_EOL;
                }
            }

        }
        else {
            return false;
        }
    }

    /**
     * Returns an array containing each entry in the directory.
     *
     * @param string @dir
     *   path to a directory.
     *
     * @return array|boolean
     */
    function getDirList($dir)
    {
        if ($dh = @opendir($dir)) {
            while (false !== ($filename = readdir($dh))) {
                if (($filename != '.')
                        && ($filename != '..')
                    ) {
                    $filelist[] = $filename;
                }
            }

            sort($filelist);

            return $filelist;
        }
        else {
            return false;
        }
    }

    /**
     * Returns an array with every file in the directory that is not a dir.
     *
     * @param string @dir
     *   path to a directory.
     *
     * @return array|boolean
     */
    function getImageList($dir)
    {
        if ($dh = @opendir($dir)) {
            while (false !== ($filename = readdir($dh))) {
                if (($filename != '.')
                        && ($filename != '..')
                        && (!is_dir($dir . $filename . DIRECTORY_SEPARATOR))
                    ) {
                    $filelist[] = $filename;
                }
            }

            sort($filelist);

            return $filelist;
        }
        else {
            return false;
        }

    }


    /**
     * Run the zoomify converter on the specified file.
     *
     * Check to be sure the file hasn't been converted already.
     * Set the perms appropriately.
     *
     * @return void
     */
    function zoomifyObject($filename, $path)
    {
        $converter = new ZoomifyFileProcessor();
        $converter->_debug = $this->_debug;
        $converter->fileMode = octdec($this->fileMode);
        $converter->dirMode = octdec($this->dirMode);
        $converter->fileGroup = $this->fileGroup;

        $trimmedFilename = $this->stripExtension($filename);

        if (!file_exists($path . $trimmedFilename)) {
            $file_to_process = $path . $filename;
            // echo "Processing " . $file_to_process . "...<br />";
            $converter->ZoomifyProcess($file_to_process);
        }
        else {
            // echo "Skipping " . $path . $filename . "... (" . $path . $trimmedFilename . " exists)<br />";
        }
    }


    /**
     * Process the specified directory.
     *
     * @return void
     */
    function processImages()
    {
        $objects = $this->getImageList(IMAGEPATH);

        foreach ($objects as $object) {
            $this->zoomifyObject($object,IMAGEPATH);
        }
    }

    /**
     * Strips the extension off of the filename, i.e. file.ext -> file
     *
     * @return string
     */
    function stripExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION)
            ? substr($filename, 0, strrpos($filename, '.'))
            : $filename;
    }
}
