<?php


/**
*   Make sure the program cookie exists and create it if it does not.
*   @param NULL
*   @return NULL
**/
function verify_program_cookie()
{
    if (isset($_COOKIE['doctor'])) return;
    else if (file_exists('.htpasswd')) $settings = array('htpasswd' => TRUE);
    else $settings = array('htpasswd' => FALSE);

    $settings['theme'] = 'default';
    $settings = json_encode($settings);
    setcookie( 'doctor', $settings, time()+(3600*24)*30, '/' );
    header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
}


/**
*   Include theme css rules
*   @param Array $settings. The file settings that may or may not contain theme settings.
*   @return NULL
**/
function add_theme_css($settings)
{
    // Define the default theme colors.
    $body = '#1F253D';
    $text = '#fff';
    $menuHover = '#50597b';
    $titles = '#11a8ab';
    $boxes = '#394264';
    $shadows = '#074142';
    $theme = 'default';
    $alertBoxes = '#1A4E95';
    $alertCircle = '#0A3269';

    //If this is not the default theme retrieve the corresponding theme colors.
    if ( $settings['theme'] != 'default' )
    {
        $areas = array('body','text','menuHover','titles','boxes','shadows','alertBoxes','alertCircle');
        foreach ($areas as $area) {
            ${$area} = $settings[$area];
        }
    }
        
    //Prepare the CSS for outputting
    ob_start();
?>
    <style>

    body, #file-name, #settings-form input[type="text"],#settings-form input[type="password"], 
    #security-form input[type="text"],#security-form input[type="password"]{ 
        background: <?php echo $body; ?>; 
    }

    h1, h2, p, a, span, td, input, label, .inline-notice, .loader-message{ color: <?php echo $text; ?>; }

    .menu-box .title, #browse tr:hover td, .sidebar-box .title, .selected-button, .loader-part{ background: <?php echo $titles; ?>; }

    th, input[type="checkbox"]:checked + label:after, #import-status > p.success:before{ color: <?php echo $titles; ?>; }
    input[type="checkbox"] + label:hover{ box-shadow: inset 1px 1px 0 0 #303030, inset 0px 1px 1px 1px #1C1C1C, inset 0px 1px 0 3px <?php echo $titles; ?>; }
    th{ border-bottom: 2px solid <?php echo $titles; ?>; }
        
    #file-name{ color: <?php echo $text; ?>; }
    .box{ background: <?php echo $boxes; ?>; }
    .theme-block:hover{ border-color: <?php echo $titles; ?>; }
    .theme-block{ border: 5px solid <?php echo $body; ?>; }
    .menu-box-tab{ border-bottom: 1px solid <?php echo $body; ?>; }
    #upload-file-button:before{ color: <?php echo $body; ?>; }
    .menu-box-tab:hover{ background: <?php echo $menuHover; ?>}
    .btn{ box-shadow: 2px 2px 30px <?php echo $shadows; ?>; }
    .theme-block:hover,
    #settings.default .theme-block[data-theme="default"],
    #settings.light .theme-block[data-theme="light"],
    #settings.dark .theme-block[data-theme="dark"]{
        cursor: pointer;
        border-color: <?php echo $titles; ?>;
    }
    .info-box .title{ background: <?php echo $alertBoxes; ?>; }
    .title .icon{ background: <?php echo $alertCircle; ?>; }

    </style>
<?php
    echo ob_get_clean();
}

verify_program_cookie();
$settings = json_decode($_COOKIE['doctor'], TRUE);
include 'functions/show_backups.php';
?>