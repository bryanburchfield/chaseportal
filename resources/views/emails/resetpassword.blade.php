<!doctype html>
<html xmlns='http://www.w3.org/1999/xhtml'>

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title>Reset Password - Chase Data Corp</title>

    <style type='text/css'>
        .ReadMsgBody {
            width: 100%;
            background-color: #ffffff;
        }

        .ExternalClass {
            width: 100%;
            background-color: #ffffff;
        }

        body {
            width: 100%;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            font-family: Georgia, Times, serif
        }

        table {
            border-collapse: collapse;
        }

        br {
            display: block;
            content: '';
            margin-top: 2px;
        }

        .btn {
          position: relative;
          width: 200px;
          height: 35px;
          margin: 0 auto;
          display: block;
          margin-bottom: 30px;
          background-color: #e49831;
          border: 1px solid #f2ad4d;
          color: #fff;
          font-weight: 600;
        }

        @media only screen and (max-width: 640px) {
            .deviceWidth {
                width: 440px !important;
                padding: 0;
            }

            .center {
                text-align: center !important;
            }
        }

        @media only screen and (max-width: 479px) {
            .deviceWidth {
                width: 280px !important;
                padding: 0;
            }

            .center {
                text-align: center !important;
            }
        }
    </style>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' style='font-family: Georgia, Times, serif'>

    <!-- Wrapper -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
        <tr>
            <td width='100%' valign='top' bgcolor='#ffffff' style='padding-top:0px'>

                <!-- Start Header-->
                <table width='580' border='0' cellpadding='0' cellspacing='0' align='center' class='deviceWidth'
                    style='margin:0 auto;'>
                    <tr>
                        <td width='100%' bgcolor='#ffffff'>

                            <!-- Logo -->
                            <table border='0' cellpadding='0' cellspacing='0' align='center' class='deviceWidth'>
                                <tr>
                                    <td style='padding:10px 20px' class='center'>
                                        <a href='#'><img src='{{ $data['url'] }}img/logo.png' alt='' border='0'
                                                style='display: block; margin:0 auto; max-width: 220px; text-align: center;' /></a>
                                    </td>
                                </tr>
                            </table><!-- End Logo -->


                        </td>
                    </tr>
                </table><!-- End Header -->
            </td>
        </tr>
    </table>

    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'
        style='margin:0 auto; background-image: url({{ $data['url'] }}img/mainBg.png); background-repeat: repeat-x;'>

        <tr>
            <td
                style='font-size: 13px; color: #959595; font-weight: normal; text-align: left; font-family: Georgia, Times, serif; line-height: 24px; vertical-align: top; padding:50px 8px 10px 8px;'>

                <table width='580' class='deviceWidth' border='0' cellpadding='0' cellspacing='0' align='center'
                    style='margin:0 auto;'>
                    <tr>

                        <td valign='middle' style='text-align:center;padding:0 10px 10px 0'>
                            <p>You are receiving this email because we received a password reset request for your account.</p>
							<a href="{{ $data['link'] }}" class="btn btn-md">Reset Password</a>
                            <p>This password reset link will expire in 60 minutes.</p>
                        	<p>If you did not request a password reset, no further action is required.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><!-- End One Column -->

    <div style='height:35px;margin:0 auto;'>&nbsp;</div><!-- spacer -->
    <!-- 4 Columns -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
        <tr>
            <td bgcolor='#203047' style='padding:30px 0'>
                <table width='580' border='0' cellpadding='10' cellspacing='0' align='center' class='deviceWidth'
                    style='margin:0 auto;'>
                    <tr>
                        <td>
                            <table width='45%' cellpadding='0' cellspacing='0' border='0' align='left'
                                class='deviceWidth'>
                                <tr>
                                    <td valign='top'
                                        style='font-size: 11px; color: #f1f1f1; color:#999; font-family: Arial, sans-serif; padding-bottom:20px'
                                        class='center'>

                                        <address
                                            style='margin-bottom:10px;line-height:17px;text-decoration: none; color: #ddd; font-weight: normal; font-style: normal;font-size: 12px;'>
                                            8201 Peters Rd Ste, 1000 <br />Plantation, FL 33324<br />(888) 739-8218
                                        </address>

                                        <a href='mailto:sales@chasedatacorp.com'
                                            style='font-size: 12px;margin-top:15px;text-decoration: none; color: #ddd; font-weight: normal;'>sales@chasedatacorp.com</a>

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

                                        <a style='margin-left:10px;'
                                            href='https://www.youtube.com/channel/UCGm2112RvM7ws3GrIq2HoPg'><img
                                                src='{{ $data['url'] }}img/youtube.png'
                                                width='32' height='32' alt='You Tube' title='You Tube' border='0' /></a>

                                        <a style='margin-left:10px;'
                                            href='https://www.linkedin.com/company/chase-data-corp/'><img
                                                src='{{ $data['url'] }}img/linkedin.png'
                                                width='32' height='32' alt='Linkedin' title='Linkedin' border='0' /></a>

                                        <a style='margin-top:10px; display: block;' href='#'><img width='160px'
                                                src='{{ $data['url'] }}img/chase_logo_blue.png'
                                                alt='' border='0' style='padding-top: 5px;' /></a><br />
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table><!-- End 4 Columns -->

    
    <div style='display:none; white-space:nowrap; font:15px courier; color:#ffffff;'>
    </div>
</body>

</html>