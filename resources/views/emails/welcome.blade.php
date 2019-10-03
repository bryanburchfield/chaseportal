<?php
$rss = new DOMDocument();
$rss->load('https://blog.chasedatacorp.com/rss.xml');
$list = array();
    
foreach ($rss->getElementsByTagName('item') as $node) {
$item = array ( 
'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
'descript' => $node->getElementsByTagName('description')->item(0)->nodeValue,
'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
);

array_push($list, $item);
}

$numberofresults = 4;    

?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Welcome  - Chase Data Corp</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800" rel="stylesheet">

    <style>
        html,
body {
    margin: 0 auto !important;
    padding: 0 !important;
    height: 100% !important;
    width: 100% !important;
    background: #f1f1f1;
}

* {
    -ms-text-size-adjust: 100%;
    -webkit-text-size-adjust: 100%;
}

div[style*="margin: 16px 0"] {
    margin: 0 !important;
}

table,
td {
    mso-table-lspace: 0pt !important;
    mso-table-rspace: 0pt !important;
}

table {
    border-spacing: 0 !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
    margin: 0 auto !important;
}

img {
    -ms-interpolation-mode:bicubic;
}

a {
    text-decoration: none;
}

*[x-apple-data-detectors],  /* iOS */
.unstyle-auto-detected-links *,
.aBn {
    border-bottom: 0 !important;
    cursor: default !important;
    color: inherit !important;
    text-decoration: none !important;
    font-size: inherit !important;
    font-family: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
}

.a6S {
    display: none !important;
    opacity: 0.01 !important;
}

.im {
    color: inherit !important;
}

img.g-img + div {
    display: none !important;
}

@media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
    u ~ div .email-container {
        min-width: 320px !important;
    }
}

@media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
    u ~ div .email-container {
        min-width: 375px !important;
    }
}

@media only screen and (min-device-width: 414px) {
    u ~ div .email-container {
        min-width: 414px !important;
    }
}

    </style>

    <style>

        .primary{
    background: #f5564e;
}
.bg_white{
    background: #ffffff;
}
.bg_light{
    background: #fafafa;
}
.bg_black{
    background: #000000;
}
.bg_dark{
    background: rgba(0,0,0,.8);
}
.email-section{
    padding:28px;
}

.bg_dark.email-section{
    padding: 10px 13px;
    font-size: 13px;
}

/*BUTTON*/
.btn{
    padding: 5px 15px;
    display: inline-block;
}
.btn.btn-primary{
    border-radius: 5px;
    background: #e49831;
    color: #ffffff;
}
.btn.btn-white{
    border-radius: 5px;
    background: #ffffff;
    color: #000000;
}
.btn.btn-white-outline{
    border-radius: 5px;
    background: transparent;
    border: 1px solid #fff;
    color: #fff;
}

h1,h2,h3,h4,h5,h6{
    font-family: 'Open Sans', sans-serif;
    color: #000000;
    margin-top: 0;
}

body{
    font-family: 'Open Sans', sans-serif;
    font-weight: 400;
    font-size: 15px;
    line-height: 1.8;
    color: rgba(0,0,0,.4);
}

a{
    color: #f5564e;
}

table{
}
/*LOGO*/

.logo h1{
    margin: 0;
}
.logo h1 a{
    color: #000;
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
    font-family: 'Open Sans', sans-serif;
}

.navigation{
    padding: 0;
}
.navigation li{
    list-style: none;
    display: inline-block;;
    margin-left: 5px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.navigation li a{
    color: rgba(0,0,0,.6);
}

/*HERO*/
.hero{
    position: relative;
    z-index: 0;
}
.hero .overlay{
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    content: '';
    width: 100%;
    background: #000000;
    z-index: -1;
    opacity: .3;
}
.hero .icon{
}
.hero .icon a{
    display: block;
    width: 60px;
    margin: 0 auto;
}
.hero .text{
    color: rgba(255,255,255,.8);
    padding: 0 4em;
}
.hero .text h2{
    color: #ffffff;
    font-size: 30px;
    margin-bottom: 0;
    line-height: 1.4;
    font-weight: 300;
}

.hero .text h2 span{font-weight: 700; font-size: 45px;}

/*HEADING SECTION*/
.heading-section{
}
.heading-section h2{
    color: #000000;
    font-size: 20px;
    margin-top: 0;
    margin-bottom: 0;
    line-height: 1.4;
    font-weight: 700;
}
.heading-section .subheading{
    margin-bottom: 20px !important;
    display: inline-block;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(0,0,0,.4);
    position: relative;
}
.heading-section .subheading::after{
    position: absolute;
    left: 0;
    right: 0;
    bottom: -10px;
    content: '';
    width: 100%;
    height: 2px;
    background: #f5564e;
    margin: 0 auto;
}

.heading-section-white{
    color: rgba(255,255,255,.8);
}
.heading-section-white h2{
    font-family: 
    line-height: 1;
    padding-bottom: 0;
}
.heading-section-white h2{
    color: #ffffff;
}
.heading-section-white .subheading{
    margin-bottom: 0;
    display: inline-block;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,.4);
}

.icon{
    text-align: center;
}
.icon img{
}

/*SERVICES*/
.services{
    background: rgba(0,0,0,.03);
}
.text-services{
    padding: 10px 10px 0; 
    text-align: center;
}
.text-services h3{
    font-size: 16px;
    font-weight: 600;
}

.services-list{
    padding: 0;
    margin: 0 0 10px 0;
    width: 100%;
    float: left;
}

.services-list .text{
    width: 100%;
    float: right;
}
.services-list h3{
    margin-top: 0;
    margin-bottom: 0;
    font-size: 18px;
}
.services-list p{
    margin: 0;
}

/*DESTINATION*/
.text-tour{
    padding-top: 10px;
}
.text-tour h3{
    margin-bottom: 0;
}
.text-tour h3 a{
    color: #000;
}

/*BLOG*/
.text-services .meta{
    text-transform: uppercase;
    font-size: 14px;
}

/*TESTIMONY*/
.text-testimony .name{
    margin: 0;
}
.text-testimony .position{
    color: rgba(0,0,0,.3);

}

/*COUNTER*/
.counter{
    width: 100%;
    position: relative;
    z-index: 0;
}
.counter .overlay{
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    content: '';
    width: 100%;
    background: #000000;
    z-index: -1;
    opacity: .3;
}
.counter-text{
    text-align: center;
}
.counter-text .num{
    display: block;
    color: #ffffff;
    font-size: 34px;
    font-weight: 700;
}
.counter-text .name{
    display: block;
    color: rgba(255,255,255,.9);
    font-size: 13px;
}

ul.social{
    padding: 0;
}
ul.social li{
    display: inline-block;
}

/*FOOTER*/

.footer .heading{
    color: #ffffff;
    font-size: 20px;
}
.footer ul{
    margin: 0;
    padding: 0;
}
.footer ul li{
    list-style: none;
    margin-bottom: 10px;
}
.footer ul li a{
    color: rgba(255,255,255,1);
}

@media screen and (max-width: 500px) {

    .icon{
        text-align: left;
    }

    .text-services{
        padding-left: 0;
        padding-right: 20px;
        text-align: left;
    }
}

body{
    background: #222222;
}

center{
    background-color: #f1f1f1;
}

tr.bg_blue{
    background-color:#203047;
}

.m0{margin:0;}
.m0auto{margin:0 auto;}
.mauto{margin: auto;}
.mt15{margin-top: 15px;}
.mb10{margin-bottom: 10px;}
.ml10{margin-left: 10px;}
.mt10{margin-top: 10px;}

.p0{padding: 0;}
.p5{padding: 5px;}
.p8{padding: 8px;}
.p10{padding: 10px;}
.p20{padding: 20px;}
.pb0{padding-bottom: 0;}
.pt5{padding-top: 5px;}
.pr20{padding-right: 20px;}

.h13{height: 13px;}
.h220{height: 220px;}


</style>


</head>

<body class="m0 p0" width="100%" style="!important; mso-line-height-rule: exactly;">
    <center style="width: 100%;">
    <div style="display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
      &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <div style="max-width: 600px; margin: 0 auto;" class="email-container">
        <!-- BEGIN BODY -->
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
            <tr>
                <td valign="top" class="bg_white p5">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="100%" class="logo" style="text-align: center;">
                                <h1><a href="#"><img src="{{ $data['url'] }}img/emaillogo.png" style="max-width:200px;" border="0" alt="" /></a></h1>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr><!-- end tr -->
            <tr>
                <td valign="middle" class="hero bg_white h220" style="background-image: url({{ $data['url'] }}img/emailbg_1.jpg); background-size: cover; ">
                    <div class="overlay"></div>
                        <table>
                        <tr>
                            <td>
                                <div class="text" style="text-align: center;">
                                    <h2 style="color:#ffffff;"><span>Explore!</span><br> ChaseData’s new Dashboard Portal</h2>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr><!-- end tr -->
            
            <tr>
                <td class="bg_dark email-section" style="text-align:center;">
                    <div class="heading-section heading-section-white">
                        <p>ChaseData’s new Dashboard Portal combines self-service discovery and data visualization through interactive performance and KPI dashboards delivering an all-in-one solution that rapidly promotes insight across your call center. Your visual dashboards portal is engineered to simplify analysis — reducing the time to consume information from hours to seconds. Harness call center-wide so you can gain unprecedented insight into your call center environment.</p>
                    </div>
                </td>
            </tr><!-- end: tr -->

            <tr>
                <td class="bg_white">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                        <tr>
                            <td class="bg_white">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td class="bg_white email-section">
                                            <div class="heading-section" style="text-align: center; padding: 0 10px 15px 10px">
                                                <h2 style="color:#e15b23;">BENEFITS OF USING THE CHASEDATA PORTAL</h2>
                                            </div>

                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td valign="top" width="50%">
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                            <tr>
                                                                <td style="padding: 10px 5px;">
                                                                    <img src="{{ $data['url'] }}img/demo1.jpg" alt="" style="width: 93%; max-width: 600px; height: auto; margin: auto; display: block;">
                                                                    <div class="text-tour" style="text-align: center;">
                                                                        <p>Improve call efficiency and track your KPIs</p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 10px 5px;">
                                                                    <img src="{{ $data['url'] }}img/demo2.jpg" alt="" style="width: 93%; max-width: 600px; height: auto; margin: auto; display: block;">
                                                                    <div class="text-tour" style="text-align: center;">
                                                                        <p>Dial in your understanding of current call center(s) performance and metrics</p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>

                                                    <td valign="top" width="50%">
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                            <tr>
                                                                <td style="padding: 10px 5px;">
                                                                    <img src="{{ $data['url'] }}img/demo4.jpg" alt="" style="width: 93%; max-width: 600px; height: auto; margin: auto; display: block;">
                                                                    <div class="text-tour" style="text-align: center;">
                                                                        <p>Pre-loaded with industry standard reports included</p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 10px 5px;">
                                                                    <img src="{{ $data['url'] }}img/demo3.jpg" alt="" style="width: 93%; max-width: 600px; height: auto; margin: auto; display: block;">
                                                                    <div class="text-tour" style="text-align: center;">
                                                                        <p>Teams understand their performance at a glance when dashboards are projected on TVs in the call center</p>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr><!-- end: tr -->
                                </table>
                            </td>
                        </tr><!-- end:tr -->
                        
                        <tr><td width="95%" style="background-color:#ccc; border:1px solid #ccc;"></td></tr>

                        <tr>
                            <td class="bg_white email-section">
                                <div class="heading-section" style="text-align: center; padding: 0 30px;">
                                    <h2 style="color:#e15b23;">OUR BLOG</h2>
                                </div>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">

                                    <?php

                                        for($i=0; $i<$numberofresults; $i++) {

                                            if($i == 0 || $i == 2){ 
                                                    echo "<tr>";  
                                            } 

                                            $title = substr($list[$i]['title'],0,55).'...';
                                            $link = $list[$i]['link'];
                                            $description = substr($list[$i]['descript'],0,600).'...';
                                            $date = date('F d, Y', strtotime($list[$i]['date']));
                                            
                                            echo '<td valign="top" width="50%">
                                            <table role="presentation" cellspacing="0" cellpadding="10" border="0" width="100%">
                                                <tr>
                                                    <td class="text-services" style="text-align: left;">
                                                        <p class="meta"><span>'.$date.'</span></p>
                                                        <h3>'.$title.'</h3>
                                                        '.$description.'
                                                        <p><a href="'.$link.'" class="btn btn-primary">Read more</a></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>';

                                            if($i == 1 || $i == 3){ 
                                                    echo "</tr>";  
                                            }

                                        }

                                    ?>
                                    
                                </table>
                            </td>
                        </tr><!-- end: tr -->
                    </table>
                </td>
            </tr><!-- end:tr -->
            <!-- 1 Column Text + Button : END -->
        </table>
    
        <!-- Begin footer-->    
            <table class="mauto" align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-image: url({{ $data['url'] }}img/footer_bg.jpg); background-size: cover; height: 150px;">
                <tr>
                    <td class="p20">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td>
                                    <table width='45%' cellpadding='0' cellspacing='0' border='0' align='left'
                                        class='deviceWidth'>
                                        <tr>
                                            <td valign='top'
                                                style='font-size: 11px; color: #f1f1f1; color:#999; font-family: Arial, sans-serif; '
                                                class='center pb0'>

                                                <address class="mb10"
                                                    style='line-height:17px;text-decoration: none; color: #ddd; font-weight: normal; font-style: normal;font-size: 12px;'>
                                                    8201 Peters Rd Ste, 1000 <br />Plantation, FL 33324<br />(888) 739-8218
                                                </address>

                                                <a href='mailto:sales@chasedatacorp.com' class="mt15"
                                                    style='font-size: 12px;text-decoration: none; color: #ddd; font-weight: normal;'>sales@chasedatacorp.com</a>

                                            </td>
                                        </tr>
                                    </table>

                                    <table width='40%' cellpadding='0' cellspacing='0' border='0' align='right'
                                        class='deviceWidth'>
                                        <tr>
                                            <td valign='top'
                                                style='font-size: 11px; color: #f1f1f1; font-weight: normal; font-family: Georgia, Times, serif; line-height: 26px; vertical-align: top; text-align:right'
                                                class='center'>

                                                <a href='https://www.facebook.com/ChaseDataCorp/'><img
                                                        src='{{ $data['url'] }}img/facebook.png'
                                                        width='32' height='32' alt='Facebook' title='Facebook' border='0' /></a>

                                                <a class="ml10"
                                                    href='https://www.youtube.com/channel/UCGm2112RvM7ws3GrIq2HoPg'><img
                                                        src='{{ $data['url'] }}img/youtube.png'
                                                        width='32' height='32' alt='You Tube' title='You Tube' border='0' /></a>

                                                <a class="ml10"
                                                    href='https://www.linkedin.com/company/chase-data-corp/'><img
                                                        src='{{ $data['url'] }}img/linkedin.png'
                                                        width='32' height='32' alt='Linkedin' title='Linkedin' border='0' /></a>

                                                <a class='mt10 pr20' style='display: block;' href='#'><img width='160px'
                                                        src='{{ $data['url'] }}img/logo-footer.png'
                                                        alt='' border='0' class='pt5' /></a><br />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table><!-- End footer-->    
    </div>
</center>
</body>
</html>