<?php
/**
 *  Upload
 */

session_start();


const UPLOADS_DIR = __DIR__.'/gallery';

class Upload
{
    public $filename;
    public $fileType;
    public $tmpName;
    public $upl_error;
    public $size;
    public $sm;
    public $md;
    public $bg;
    public $has_thumb;
    public $targetPath = UPLOADS_DIR;

    function __construct( $file = false ) {
        if( isset( $_FILES ) && !empty($_FILES) ) {
            $this->file_processor($file);
            // $this->upl_errors($file);
        }
    }

    public function file_processor( $file ) {
        // $this->targetPath = $targetPath;

//        print_r($file);


        $targetPath = UPLOADS_DIR . '/';

        $path_to_thumbs_directory = UPLOADS_DIR.'/thumbs/';
        if ( isset($_FILES) && !empty( $_FILES ) ) {
            foreach ((array)$file as $key => $files_value) {


                $filename = $file[$key]['name'];
                $fileType = $file[$key]['type'];
                $tmpName = $file[$key]['tmp_name'];
                $upl_error = $file[$key]['error'];
                $size = $file[$key]['size'];

            }
            $this -> filename = strtolower($filename);
            $this -> fileType = $fileType;
            $this -> tmpName = $tmpName;
            $this -> upl_error = $upl_error;
            $this -> size = $size;
            #
            # Multiple file inputs with name='file[]'
            #
            if( sizeof ( $file[$key]['name'] ) > 0 && is_array( $file[$key]['name'] )  ) {
                foreach ($filename as $key => $value) {

                    if( $filename[$key] != '' ) {
                        $ext = strtolower(pathinfo($filename[$key], PATHINFO_EXTENSION));
                        $target_file = strtolower($targetPath . basename($filename[$key]) );
                        $cleanName = strtolower(pathinfo($target_file,PATHINFO_FILENAME));
                        $check = getimagesize($tmpName[$key]);
                        $image_type = $check['mime'];
                    }
                    // if( $image_type == 'image/jpeg' || $image_type == 'image/png' || $image_type == 'image/gif' ) {
                    //   echo 'Its an IMAGE!';
                    // }

                    if( $upl_error[$key] == UPLOAD_ERR_INI_SIZE ) {
                        $_SESSION['errors'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                    }
                    else if( $upl_error[$key] == UPLOAD_ERR_FORM_SIZE )
                    {
                        $_SESSION['errors'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
                    } else if( $upl_error[$key] == UPLOAD_ERR_PARTIAL )
                    {
                        $_SESSION['errors'] = 'The uploaded file was only partially uploaded.';
                    } else if( $upl_error[$key] == UPLOAD_ERR_NO_FILE && !is_uploaded_file( $filename[$key] ) )
                    {
                        $_SESSION['errors'] = 'No file was uploaded. Choose the file';
                    } else if( $upl_error[$key] == UPLOAD_ERR_NO_TMP_DIR )
                    {
                        $_SESSION['errors'] = 'Missing a temporary folder.';
                    } else if( $upl_error[$key] == UPLOAD_ERR_CANT_WRITE )
                    {
                        $_SESSION['errors'] = 'Failed to write file to disk.';
                    } else if( $upl_error[$key] == UPLOAD_ERR_EXTENSION )
                    {
                        $_SESSION['errors'] = 'File upload stopped by extension.';
                    }
                    else if( $upl_error[$key] == UPLOAD_ERR_OK && $tmpName[$key] !== false  ) {
                        # If no errors continue
                        if( $image_type == 'image/jpeg' || $image_type == 'image/png' || $image_type == 'image/gif' )
                        {
                            # RENAME FILE IF EXISTS
                            if ( file_exists( $target_file ) ) {
                                $cleanName = str_replace(' ', '_', time() .'-'. strtolower(pathinfo($target_file,PATHINFO_FILENAME)));
                                move_uploaded_file($tmpName[$key], $targetPath . $cleanName.'.'.$ext );
                                $cleanName_arr[] = $cleanName.'.'.$ext;
                            } else {
                                # MOVES UPLOADED FILE WITHOUR RENAMING
                                move_uploaded_file($tmpName[$key], $targetPath . basename( strtolower($filename[$key]) ) );
                                $cleanName_arr[] = $cleanName.'.'.$ext;
                            }
                            if($ext == 'jpg') {
                                $im = imagecreatefromjpeg($targetPath . $cleanName.'.'.$ext);
                            } else if ($ext == 'gif') {
                                $im = imagecreatefromgif($targetPath . $cleanName.'.'.$ext);
                            } else if ( $ext == 'png' ) {
                                $im = imagecreatefrompng($targetPath . $cleanName.'.'.$ext);
                            } else {
                                $_SESSION['errors'] = 'Images can be only jpg , png or gif';
                            }
                            # Array of thumbnails
                            $thumbsAray = [ 480 => '480' , 780 => '780' ,1024 => '1024' ];
                            #get original image dimensions
                            $ox = imagesx($im);
                            $oy = imagesy($im);
                            # Create Thumbnails here from values on array
                            foreach ($thumbsAray as $thumb_key => $value) {
                                # if uploaded image is not small than minimum thumbnail
                                # thumbnails are created.
                                # has_thumbs = 1
                                # else has_thumbs = 0
                                $ratio = ($ox / $oy);
                                if ($ox >= $thumbsAray[480]) {
                                    $nx = $thumbsAray[$thumb_key];
                                    $nx = floor($ox * ($value / $oy));
                                    $ny = floor($oy * ($value / $ox));
                                    if ( $ratio > 1 ) {
                                        // $ny = floor($oy * ($value / $ox));
                                        $nm = imagecreatetruecolor($value, $value);
                                        if ( $nx > $thumbsAray[$thumb_key] ) {
                                            # Center by height ( Y AXIS )
                                            imagecopyresampled($nm, $im, -($nx - $value)/2,0,0,0,$nx,$value,$ox,$oy);
                                        }
                                    }
                                    else if( $ratio < 1 ) {
                                        # Center by Width ( X AXIS )
                                        $nm = imagecreatetruecolor($value, $value);
                                        imagecopyresampled($nm, $im,0, -($ny-$value)/2 ,0,0,$value,$ny,$ox,$oy);
                                    } else if( $ratio == 1 ) {
                                        # No need to resize
                                        $nm = imagecreatetruecolor($value, $value);
                                        imagecopyresampled($nm, $im, 0,0,0,0,$nx,$value,$ox,$oy);
                                    }
                                    if($ext == 'jpg') {
                                        imagejpeg($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                    } else if ( $ext == 'png' ) {
                                        imagepng($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                    } else {
                                        imagegif($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                    }
                                }
                            }
                            if ($ox >= $thumbsAray[480]) {
                                $has_thumb[] = 1;
                            } else {
                                $has_thumb[] = 0;
                            }
                            foreach( $has_thumb as $thumb_c ) {}
                            if( $thumb_c == 1 ) {
                                $sm[] = $cleanName.'-'.$thumbsAray[480].'.'.$ext;
                                $md[] = $cleanName.'-'.$thumbsAray[780].'.'.$ext;
                                $bg[] = $cleanName.'-'.$thumbsAray[1024].'.'.$ext;
                                print_r($sm).'<br/>';
                                print_r($md).'<br/>';
                                print_r($bg).'<br/>';
                            } else {
                                $sm[] = '';
                                $md[] = '';
                                $bg[] = '';
                                // echo 'No Thumbnail Created';
                            }


                            # FIELDS TO OUTPUT FOR INSERT
                            $this->has_thumb = $has_thumb;
                            $this->sm = $sm;
                            $this->md = $md;
                            $this->bg = $bg;
                            $this->cleanname = $cleanName_arr;
                            $this->filename = $filename;

                            $_SESSION['errors'] = "";
                        } else {
                            $_SESSION['errors'] = 'File is not an image';
                        }
                    }

                }
            } # END OF Multiple file inputs with name='file[]'
            else
            {
                #
                # Sinlge file input field
                #
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $target_file = strtolower( $targetPath . basename($filename) );

                $cleanName = strtolower(pathinfo($target_file,PATHINFO_FILENAME));
                $check = getimagesize($tmpName);
                $image_type = $check['mime'];
                if( $upl_error == UPLOAD_ERR_INI_SIZE )
                {
                    $_SESSION['errors'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                } else if( $upl_error == UPLOAD_ERR_FORM_SIZE )
                {
                    $_SESSION['errors'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
                } else if( $upl_error == UPLOAD_ERR_PARTIAL )
                {
                    $_SESSION['errors'] = 'The uploaded file was only partially uploaded.';
                } else if( $upl_error == UPLOAD_ERR_NO_FILE )
                {
                    $_SESSION['errors'] = 'No file was uploaded. Choose the file';
                } else if( $upl_error == UPLOAD_ERR_NO_TMP_DIR )
                {
                    $_SESSION['errors'] = 'Missing a temporary folder.';
                } else if( $upl_error == UPLOAD_ERR_CANT_WRITE )
                {
                    $_SESSION['errors'] = 'Failed to write file to disk.';
                } else if( $upl_error == UPLOAD_ERR_EXTENSION )
                {
                    $_SESSION['errors'] = 'File upload stopped by extension.';
                }
                else if( $upl_error == UPLOAD_ERR_OK && $tmpName !== false  ) {

                    $_SESSION['errors'] = "";

                    if( $image_type == 'image/jpeg' || $image_type == 'image/png' || $image_type == 'image/gif' )

                    {
                        # RENAME FILE IF EXISTS
                        if ( file_exists( $target_file ) ) {
                            $cleanName = str_replace(' ', '_', time() .'-'. strtolower(pathinfo($target_file,PATHINFO_FILENAME)));
                            move_uploaded_file($tmpName, $targetPath . $cleanName.'.'.$ext );

                        } else {
                            # MOVES UPLOADED FILE WITHOUR RENAMING
                            move_uploaded_file($tmpName, $targetPath . basename( strtolower($filename) ) );
                        }
                        # Checking for file type
                        if( $ext == 'jpg' ) {
                            $im = imagecreatefromjpeg($targetPath . $cleanName.'.'.$ext);
                        } else if ($ext == 'gif') {
                            $im = imagecreatefromgif($targetPath . $cleanName.'.'.$ext);
                        } else if ( $ext == 'png' ) {
                            $im = imagecreatefrompng($targetPath . $cleanName.'.'.$ext);
                        } else {
                            $_SESSION['errors'] = 'Images can be only jpg , png or gif';
                        }
                        # Array of thumbnails
                        $thumbsAray = [ 480 => '480' , 780 => '780' ,1024 => '1024' ];
                        #get original image dimensions
                        $ox = imagesx($im);
                        $oy = imagesy($im);

                        print_r($im);

                        # Create Thumbnails here from values on array
                        foreach ($thumbsAray as $thumb_key => $value) {
                            # if uploaded image is not small than minimum thumbnail
                            # thumbnails are created.
                            # has_thumbs = 1
                            # else has_thumbs = 0
                            $ratio = ($ox / $oy);

                            if ($ox >= $thumbsAray[480]) {
                                $nx = $thumbsAray[$thumb_key];
                                $nx = floor($ox * ($value / $oy));
                                $ny = floor($oy * ($value / $ox));
                                if ( $ratio > 1 ) {
                                    // $ny = floor($oy * ($value / $ox));
                                    $nm = imagecreatetruecolor($value, $value);
                                    if ( $nx > $thumbsAray[$thumb_key] ) {
                                        # Center by height ( Y AXIS )
                                        imagecopyresampled($nm, $im, -($nx - $value)/2,0,0,0,$nx,$value,$ox,$oy);
                                    }
                                }
                                else if( $ratio < 1 ) {
                                    # Center by Width ( X AXIS )
                                    $nm = imagecreatetruecolor($value, $value);
                                    imagecopyresampled($nm, $im,0, -($ny-$value)/2 ,0,0,$value,$ny,$ox,$oy);
                                } else if( $ratio == 1 ) {
                                    # No need to resize
                                    $nm = imagecreatetruecolor($value, $value);
                                    imagecopyresampled($nm, $im, 0,0,0,0,$nx,$value,$ox,$oy);
                                }
                                if($ext == 'jpg') {
                                    imagejpeg($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                } else if ( $ext == 'png' ) {
                                    imagepng($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                } else {
                                    imagegif($nm, $path_to_thumbs_directory .$cleanName.'-'.$value.'.'.$ext);
                                }
                            }
                        }
                        if ($ox >= $thumbsAray[480]) {
                            $has_thumb = 1;
                        } else {
                            $has_thumb = 0;
                        }
                        if( $has_thumb == 1 ) {
                            $sm = $cleanName.'-'.$thumbsAray[480].'.'.$ext;
                            $md = $cleanName.'-'.$thumbsAray[780].'.'.$ext;
                            $bg = $cleanName.'-'.$thumbsAray[1024].'.'.$ext;
                            echo $sm.'<br/>';
                            echo $md.'<br/>';
                            echo $bg.'<br/>';
                        } else {
                            $sm = '';
                            $md = '';
                            $bg = '';
                            echo 'No Thumbnail Created';
                        }
                        $this->has_thumb = $has_thumb;
                        $this->sm = $sm;
                        $this->md = $md;
                        $this->bg = $bg;
                        $this->cleanname = $cleanName;
                    } else
                    {
                        $_SESSION['errors'] = 'File is not an image';
                    }
                    # Create Thumbnails here
                }
                else {
                    $_SESSION['errors'] = 'Uploaded Successfully';
                }
                echo $filename;
            }
            #END OF SINLGE FILE HANDLE
        }
        else
        {
            $_SESSION['errors'] = 'File not uploaded due to errors';
        }
        if (isset($_SESSION['errors'])) {
            print_r($_SESSION['errors']);
        }
    }
    # END FILE PROCESSOR

}

?>
