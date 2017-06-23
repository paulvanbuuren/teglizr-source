<?php

///
// Tegelizr - filecheck.inc.php
// ----------------------------------------------------------------------------------
// checkt de thumbs en tegeltjes op consistentie
// ----------------------------------------------------------------------------------
// @author  Paul van Buuren
// @license GPL-2.0+
// @version 7.0.3
// @desc.   Favicon. Tegeldatum.
// @link    https://github.com/paulvanbuuren/tegelizr-source
///


if ( TEGELIZR_DEBUG ) {
//  $outputyesno = true;
  $outputyesno = false;
}
else {
  $outputyesno = false;
}

  
function checkthreefiles( $type = '', $keyfilename = '',  $thumbfilename = '' ) {

  global $sourcefiles_thumbs;
  global $sourcefiles_tegels;
  global $deletedfiles_thumbs;
  global $deletedfiles_tegels;
  global $path;
  global $outputyesno;
  

  $groot_image    = $sourcefiles_tegels . $keyfilename . '.png';
  $groot_txt      = $sourcefiles_tegels . $keyfilename . '.txt';
  $thumb_image    = $sourcefiles_thumbs . $thumbfilename . '.png';

  if ( $thumbfilename ) {
    // heuh, wat was je denkende?
    
  }
  else {


    $views            = getviews($groot_txt,true);
    $thumbfilename    = isset($views['file_thumb']) ? $views['file_thumb'] : '';


    $replace        = $sourcefiles_thumbs;
    $with           = '';
    $pattern        = '|' . $replace . '|i';
    $thumbfilename  = preg_replace($pattern, $with, $thumbfilename);

    $replace        = '/home/paulvanb/webapps/tegelizr/thumbs/';
    $with           = '';
    $pattern        = '|' . $replace . '|i';
    $thumbfilename  = preg_replace($pattern, $with, $thumbfilename);

    $with           = '';
    $pattern        = '|(\.png)|i';
    $thumbfilename  = preg_replace($pattern, $with, $thumbfilename);


    $thumb_image      = $sourcefiles_thumbs . $thumbfilename . '.png';

        
  }
  

  // als de grote plaat bestaat en het txt-bestand en de thumbnail, niks doen
  if ( file_exists( $groot_image ) && file_exists( $groot_txt )  && file_exists( $thumb_image ) ) {
    return true;
  }
  else {

    dodebug('<p style="background: red; color: white;">Woeps (' . $type . ')</p><ul>', $outputyesno );
    if (! file_exists( $groot_image ) ) {
      dodebug('<li>De grote plaat bestaat niet (' . $groot_image . ')</li>', $outputyesno );
    }
    if (! file_exists( $groot_txt ) ) {
      dodebug('<li>Het txt-bestand bestaat niet (' . $groot_txt . ')</li>', $outputyesno );
    }
    if (! file_exists( $thumb_image ) ) {
      dodebug('<li>De thumbnail bestaat niet (' . $thumb_image . ')</li>', $outputyesno );
    }
    dodebug('</ul>', $outputyesno );
      

    if ( file_exists( $groot_image ) ) {
      $newfile  = $deletedfiles_tegels . $keyfilename . '.png';
      rename( $groot_image, $newfile );
    }
    
    if ( file_exists( $groot_txt ) ) {
      $newfile  = $deletedfiles_tegels . $keyfilename . '.txt';
      rename( $groot_txt, $newfile );
    }

    if ( file_exists( $thumb_image ) ) {
      $newfile  = $deletedfiles_thumbs . $thumbfilename . '.png';
      rename( $thumb_image, $newfile );
    }

    
    return false;
  }

  return true;

}

  
function redirect_naar_verbetermetadatascript( $redirect = '' ) {


  if ( $redirect ) {
    // doorsturen naar pagina met het bestaande image
    header('Location: ' . $redirect);    
  }  

}


function verbeteralletegelmetadata( $redirect = '' ) {
  
  global $sourcefiles_thumbs;
  global $sourcefiles_tegels;
  global $deletedfiles_thumbs;
  global $deletedfiles_tegels;
  global $path;
  global $outputyesno;


  $images         = '';
  $list           = '';
  $tegelcounter   = 0;
  $errorcounter   = 0;
  $returnarray    = array();
  $boom           = array();


  if ( is_dir($sourcefiles_thumbs) && is_dir($sourcefiles_tegels) && is_dir($deletedfiles_thumbs)  && is_dir($deletedfiles_tegels) ) {
    
    // eerst de thumbs opruimen
    $thumbs         = glob($sourcefiles_thumbs . "*.png");
    $replace        = $sourcefiles_thumbs;
    $with           = '';
    $pattern        = '|' . $replace . '|i';
    $thumbs         = preg_replace($pattern, $with, $thumbs);
    
    $with           = '';
    $pattern        = '|(\.png)|i';
    $thumbs         = preg_replace($pattern, $with, $thumbs);
    
    rsort($thumbs);
    
    foreach( $thumbs as $thethumb) {

      // door alle thumbs heen lopen
      $stack          = explode('/', $thethumb);
      $thumb_filename = array_pop($stack);
      $info           = explode('_', $thumb_filename );

      checkthreefiles( 'thumbs', $info[1], $thethumb );
      
      // check of in het .txt bestand wel echt deze thumb als thumb genoteerd staat

      $groot_txt      = $sourcefiles_tegels . $info[1] . '.txt';
      $views          = getviews($groot_txt,true);
      $thumbfilename_x  = isset($views['file_thumb']) ? $views['file_thumb'] : '';

      $replace        = $sourcefiles_thumbs;
      $with           = '';
      $pattern        = '|' . $replace . '|i';
      $thumbfilename  = preg_replace($pattern, $with, $thumbfilename_x);
  
      $replace        = '/home/paulvanb/webapps/tegelizr/thumbs/';
      $with           = '';
      $pattern        = '|' . $replace . '|i';
      $thumbfilename  = preg_replace($pattern, $with, $thumbfilename);
      
      if ( $thumbfilename == $thethumb . '.png') {
        // deze thumbnail mag blijven staan
      }
      else {
        dodebug('<p>' . $thethumb . ' = deze thumbnail MOET foetsie:<ul><li>Huidige bestand: ' . $thethumb . '</li><li>Txt + tegel: ' .  $info[1] . '.png</li><li>En die verwijst naar: ' . $thumbfilename_x . '</li></ul>', $outputyesno );

        $thumb_image  = $sourcefiles_thumbs . $thethumb . '.png';
        $newfile      = $deletedfiles_thumbs . $thethumb . '.png';
        dodebug('Van<br><em>' . $thumb_image . '</em><br>naar<br><em>' . $newfile . '</em></p>', $outputyesno );
        rename( $thumb_image, $newfile );

      }
      
    }

    // dan de tegeltjes opruimen
    $tegeltjes      = glob($sourcefiles_tegels . "*.png");
    $replace        = $sourcefiles_tegels;
    $with           = '';
    $pattern        = '|' . $replace . '|i';
    $tegeltjes      = preg_replace($pattern, $with, $tegeltjes);
    
    $with           = '';
    $pattern        = '|(\.png)|i';
    $tegeltjes      = preg_replace($pattern, $with, $tegeltjes);
    
    rsort($tegeltjes);

    
    foreach($tegeltjes as $tegeltje) {

      // door alle thumbs heen lopen
      $stack          = explode('/', $tegeltje);
      $thumb_filename = array_pop($stack);
      $info           = explode('_', $thumb_filename );

      checkthreefiles( 'echte tegeltjes', $tegeltje );

    }


    // dan de tekstbestanden opruimen
    $textfiles      = glob($sourcefiles_tegels . "*.txt");
    $replace        = $sourcefiles_tegels;
    $with           = '';
    $pattern        = '|' . $replace . '|i';
    $textfiles      = preg_replace($pattern, $with, $textfiles);
    
    $with           = '';
    $pattern        = '|(\.txt)|i';
    $textfiles      = preg_replace($pattern, $with, $textfiles);
    
    rsort($textfiles);

    
    foreach($textfiles as $textfile) {

      // door alle textbestanden heen lopen
      $stack          = explode('/', $textfile);
      $thumb_filename = array_pop($stack);
      $info           = explode('_', $thumb_filename );

      checkthreefiles( 'echte textfiles', $textfile );

    }

    // na het opruimen gaan we alle tegeltjes voorzien van de juiste links naar de tegels VOOR en NA de thumbnail
    $loopcounter = 0;

    dodebug('<h1>Vorige en volgende</h1><ul>', $outputyesno );    

    $returnfirst_titel  = '';
    $returnfirst_url    = '';
    
    foreach( $thumbs as $thethumb) {

      // door alle thumbs heen lopen

      $stack          = explode('/', $thethumb);
      $thumb_filename = array_pop($stack);
      $info           = explode('_', $thumb_filename );

      $vorigenr       = ( $loopcounter - 1);
      $volgendenr     = ( $loopcounter + 1);
      
      $groot_txt      = $sourcefiles_tegels . $info[1] . '.txt';

      $json_data      = file_get_contents( $groot_txt );
      $all            = json_decode($json_data, true);

      if( $all ) {

        $huidige        = $thumbs[$loopcounter] . '.png';      
        $vorige         = ( isset( $thumbs[$vorigenr] ) ) ? $thumbs[$vorigenr] : '';      
        $volgende       = ( isset( $thumbs[$volgendenr] ) ) ? $thumbs[$volgendenr] : '';      

        $boom[$thethumb]                          = '';
        $boom[$thethumb]['file_thumb']            = $huidige;
        $boom[$thethumb]['file_date_readable']    = strftime('%e %B %Y',$date);
        $boom[$thethumb]['txt_tegeltekst']        = $all['txt_tegeltekst'];
        $boom[$thethumb]['file_name']             = $all['file_name'];
        $boom[$thethumb]['file_thumb']            = $all['file_thumb'];
        $boom[$thethumb][TEGELIZR_VIEWS]          = $all[TEGELIZR_VIEWS];

        if ( $vorige ) {
          $vorige         = explode('_', $vorige );
          $vorige         = $vorige[1];
        }

        if ( $volgende ) {
          $volgende       = explode('_', $volgende );
          $volgende       = $volgende[1];
        }

        dodebug('<li><strong>' . $huidige . '</strong> (' . $loopcounter . ')<ul><li>vorige: ' . $vorige . '</li><li>volgende: ' . $volgende . '</ul></li>', $outputyesno );
      
        $all['file_thumb']        = $huidige;

        unset( $all['vorige'] );
        unset( $all['vorige_titel'] );
        unset( $all['volgende'] );
        unset( $all['volgende_titel'] );
        unset( $all['laatst_bijgewerkt'] );
        
        if ( $vorige ) {
          $vorige_views             = getviews( $sourcefiles_tegels . $vorige . '.txt',true);
          $all['vorige']            = $vorige;
          $all['vorige_titel']      = isset($vorige_views['txt_tegeltekst']) ? $vorige_views['txt_tegeltekst'] : $vorige;
        }
        if ( $volgende ) {
          $volgende_views           = getviews( $sourcefiles_tegels . $volgende . '.txt',true);
          $all['volgende']          = $volgende;
          $all['volgende_titel']    = isset($volgende_views['txt_tegeltekst']) ? $volgende_views['txt_tegeltekst'] : $volgende;
        }

        if ( 1 == $loopcounter ) {
          $returnfirst_titel  = $all['vorige_titel'];
          $returnfirst_url    = $all['vorige'];
        }

        $all['laatst_bijgewerkt']   = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

      
        $newJsonString = json_encode($all);
        file_put_contents( $groot_txt, $newJsonString);
        
      }

      $loopcounter++;


    }
    
    dodebug('</ul>', $outputyesno );    

    
    if ( $returnfirst_titel && $returnfirst_titel ) {
      $returnarray[TEGELIZR_JS_NAV_NEXTKEY]   = '<a class="volgende" href="' . TEGELIZR_PROTOCOL . $_SERVER['HTTP_HOST'] . '/' . TEGELIZR_SELECTOR . '/' . $returnfirst_url . '" title="Bekijk \'' . $returnfirst_titel . '\'">' . $returnfirst_titel . '<span class="pijl">&#10157;</span></a>';
    }

    $fh             = fopen( TEGELIZR_ALL_DB, 'w') or die("can't open file: " . TEGELIZR_ALL_DB);
    $stringData     = json_encode( $boom );

    fwrite($fh, $stringData);
    fclose($fh);

    return $returnarray;
    

  }
  else {



//    dodebug('Een folderprobleem.<br>', $outputyesno );    
    
    if ( ! is_dir($sourcefiles_thumbs) ) {
      if (!mkdir($sourcefiles_thumbs, 0777, true)) {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Kan folder niet aanmaken: ' . $sourcefiles_thumbs;
      }
      else {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Folder bestond niet, maar is nu aangemaakt: ' . $sourcefiles_thumbs;
      }
    }
    
    if ( ! is_dir($sourcefiles_tegels) ) {
      if (!mkdir($sourcefiles_tegels, 0777, true)) {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Kan folder niet aanmaken: ' . $sourcefiles_tegels;
      }
      else {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Folder bestond niet, maar is nu aangemaakt: ' . $sourcefiles_tegels;
      }
    }
    else {
    }
    
    if ( ! is_dir($deletedfiles_thumbs) ) {
      if (!mkdir($deletedfiles_thumbs, 0777, true)) {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Kan folder niet aanmaken: ' . $deletedfiles_thumbs;
      }
      else {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Folder bestond niet, maar is nu aangemaakt: ' . $deletedfiles_thumbs;
      }
    }

    
    if ( ! is_dir($deletedfiles_tegels) ) {
      if (!mkdir($deletedfiles_tegels, 0777, true)) {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Kan folder niet aanmaken: ' . $deletedfiles_tegels;
      }
      else {
        $returnarray[TEGELIZR_JS_SCRIPTERROR]   = 'Folder bestond niet, maar is nu aangemaakt: ' . $deletedfiles_thumbs;
      }
    }
    

    return $returnarray;

  }

  
}
  