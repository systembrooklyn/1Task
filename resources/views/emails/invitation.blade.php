{{-- <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Team Invitation</title>
      <style>
          body {
              font-family: Arial, sans-serif;
              background-color: #8A64EB;
              color: #BBE8FF;
              margin: 0;
              padding: 0;
          }
          .logo {
              text-align: left;
              margin-right: 20px;
              margin-top: 10px;
          }
         .logo img {
              width: 200px; /* Smaller logo size */
              height: auto;
          }
          .container {
              max-width: 600px;
              margin: 0px auto;
              background-color: #ffffff;
              padding: 0px;
              border-radius: 10px;
              box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
              text-align: center;
          }
          .header {
              font-size: 24px;
              font-weight: bold;
              margin-bottom: 10px;
              color: #8A64EB; /* Change color to #8A64EB */
          }
          .sub-header {
              font-size: 16px;
              font-weight: bold;
              color: #8A64EB; /* Set color to #AF4EBE */
              margin-bottom: 20px;
              line-height: 1.6;
          }
          .description {
              font-size: 16px;
              color: #BCA7F2; /* Set color to #AF4EBE */
              margin-bottom: 20px;
              line-height: 1.6;
          }
          .button-container {
              margin: 20px 0;
          }
          .button {
             background-color: #8A64EB; /* Keep the existing background color */
             color: #ffffff !important; /* Set text color to white and use !important to override other styles */
             font-weight: bold; /* Make the text bold */
             padding: 15px 30px;
             text-decoration: none;
             font-size: 16px;
             border-radius: 5px;
             box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          }
          .button:hover {
              color: #8767E4; /* Ensure the text remains white on hover */
              background-color: #C9BAF6; /* Background color changes on hover */
          }
          .footer {
              font-size: 12px;
              color: #8868E5;
              margin-top: 40px;
          }
          .team-note {
              font-size: 10pt; /* Same size as footer text */
              color: #26C1F9; /* Blue color */
              margin-top: 20px;
          }
          .copright-note {
              font-size: 10pt; /* Same size as footer text */
              color: #BBBBBB; /* Blue color */
              margin-top: 20px;
          }
          .expiry-note {
              color: #B3261E; /* Red color */
              font-size: 10pt; /* Smaller font size */
              margin-top: 10px;
              display: block;
              font-weight: bold; /* Make the text bold */
          }
      </style>
  </head>
  <body>
      <div class="logo">
          <img src="https://drive.google.com/uc?export=view&id=1lhka1jmaUXL9_Aq_EnogTzgtFuwi60rj" alt="Task-Com Logo" />
      </div>
      <div class="container">
          <div>
              <img src="https://drive.google.com/uc?export=view&id=17Ea23eFBJLFgakwYHpW1EHY3s-9BcNpm" alt="Invitation Image" style="width: 250px; height: auto; margin-bottom: 20px;" />
          </div>
          <div class="header">You're invited to Task-Com Program</div><br>
          <div class="sub-header">...... has invited you to join Company name's Team on Task-Com.</div>
          <div class="description">
              New to Task-Com? You can manage your assigned tasks by filtering them based on deadlines, urgency, under review, and more features.<br><br>
              Joining a team on Task-Com makes working together super easy.
          </div>
          <br>
          <div class="button-container">
            <a href="https://www.1task.net/user-information#token={{ $invitation->token }}&expires_at={{ $invitation->expires_at->timestamp }}" class="button">Accept Invitation</a>
          </div>
          <br>
          <div class="expiry-note">Note: This Invitation link will expire after one hour<br>
          </div>
          <br>
          <p class="team-note">Task-Com Team</p>
          <p class="copright-note">© 2024, Task-Com managing your tasks</p>
          <br><br>
      </div>
  </body>
  </html>
  `;


 --}}

<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title></title>
    <style type="text/css">
        @media only screen and (min-width: 620px) {
            .u-row {
                width: 600px !important;
            }

            .u-row .u-col {
                vertical-align: top;
            }

            .u-row .u-col-33p33 {
                width: 199.98px !important;
            }

            .u-row .u-col-38p67 {
                width: 232.02px !important;
            }

            .u-row .u-col-61p33 {
                width: 367.98px !important;
            }

            .u-row .u-col-66p67 {
                width: 400.02px !important;
            }

            .u-row .u-col-100 {
                width: 600px !important;
            }
        }

        @media only screen and (max-width: 620px) {
            .u-row-container {
                max-width: 100% !important;
                padding-left: 0px !important;
                padding-right: 0px !important;
            }

            .u-row {
                width: 100% !important;
            }

            .u-row .u-col {
                display: block !important;
                width: 100% !important;
                min-width: 320px !important;
                max-width: 100% !important;
            }

            .u-row .u-col>div {
                margin: 0 auto;
            }

            .u-row .u-col img {
                max-width: 100% !important;
            }
        }

        body {
            margin: 0;
            padding: 0;

        }

        table,
        td,
        tr {
            border-collapse: collapse;
            vertical-align: top;
        }

        .ie-container table,
        .mso-container table {
            table-layout: fixed;
        }

        * {
            line-height: inherit;
        }

        a[x-apple-data-detectors="true"] {
            color: inherit !important;
            text-decoration: none !important;
        }

        table,
        td {
            color: #000000;
        }

        @media (max-width: 480px) {
            #u_column_7 .v-col-border {
                border-top: 0px solid transparent !important;
                border-left: 0px solid transparent !important;
                border-right: 0px solid transparent !important;
                border-bottom: 0px solid transparent !important;
            }

            #u_content_heading_10 .v-container-padding-padding {
                padding: 5px 10px 20px !important;
            }

            #u_content_heading_10 .v-text-align {
                text-align: center !important;
            }

            #u_content_menu_1 .v-padding {
                padding: 0px 9px 12px !important;
            }

            #u_content_heading_1 .v-container-padding-padding {
                padding: 40px 10px 0px !important;
            }

            #u_content_heading_1 .v-text-align {
                text-align: center !important;
            }

            #u_content_heading_2 .v-container-padding-padding {
                padding: 0px 10px !important;
            }

            #u_content_heading_2 .v-font-size {
                font-size: 24px !important;
            }

            #u_content_heading_2 .v-text-align {
                text-align: center !important;
            }

            #u_content_image_1 .v-container-padding-padding {
                padding: 10px !important;
            }

            #u_content_heading_4 .v-container-padding-padding {
                padding: 30px 10px 0px !important;
            }

            #u_content_heading_4 .v-text-align {
                text-align: center !important;
            }

            #u_content_text_2 .v-container-padding-padding {
                padding: 5px 10px 10px !important;
            }

            #u_content_text_2 .v-text-align {
                text-align: center !important;
            }

            #u_content_button_1 .v-text-align {
                text-align: center !important;
            }

            #u_content_button_1 .v-container-padding-padding {
                padding: 10px 10px 50px !important;
            }

            #u_content_heading_3 .v-container-padding-padding {
                padding: 30px 10px 5px !important;
            }

            #u_content_heading_3 .v-text-align {
                text-align: center !important;
            }

            #u_content_text_1 .v-container-padding-padding {
                padding: 0px 10px 30px !important;
            }

            #u_content_text_1 .v-text-align {
                text-align: center !important;
            }

            #u_content_heading_5 .v-container-padding-padding {
                padding: 30px 10px 5px !important;
            }

            #u_content_heading_5 .v-text-align {
                text-align: center !important;
            }

            #u_content_text_3 .v-container-padding-padding {
                padding: 0px 10px 30px !important;
            }

            #u_content_text_3 .v-text-align {
                text-align: center !important;
            }

            #u_content_heading_6 .v-container-padding-padding {
                padding: 30px 20px 10px !important;
            }

            #u_content_heading_6 .v-text-align {
                text-align: center !important;
            }

            #u_content_text_4 .v-container-padding-padding {
                padding: 0px 20px 10px !important;
            }

            #u_content_text_4 .v-text-align {
                text-align: center !important;
            }

            #u_content_social_1 .v-container-padding-padding {
                padding: 10px 0px 10px 80px !important;
            }

            #u_content_heading_8 .v-container-padding-padding {
                padding: 15px 10px 10px !important;
            }

            #u_content_heading_8 .v-text-align {
                text-align: center !important;
            }

            #u_content_heading_9 .v-container-padding-padding {
                padding: 5px 10px 20px !important;
            }

            #u_content_heading_9 .v-text-align {
                text-align: center !important;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700&display=swap" rel="stylesheet"
        type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap" rel="stylesheet"
        type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@500&display=swap" rel="stylesheet"
        type="text/css" />
</head>

<body class="clean-body u_body"
    style="
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: 100%;
      background-color: #ecf0f1;
      color: #000000;
    ">
    <div class="u-row-container" style="padding: 0px; background-color: transparent">
        <div class="u-row"
            style="
          margin: 0 auto;
          min-width: 320px;
          max-width: 600px;
          overflow-wrap: break-word;
          word-wrap: break-word;
          word-break: break-word;
          background-color: transparent;
        ">
            <div
                style="
            border-collapse: collapse;
            display: table;
            width: 100%;
            height: 100%;
            background-color: transparent;
          ">
                <div class="u-col u-col-100"
                    style="
              max-width: 320px;
              min-width: 600px;
              display: table-cell;
              vertical-align: top;
            ">
                    <div
                        style="
                background-color: #ffffff;
                height: 100%;
                width: 100% !important;
              ">
                        <div class="v-col-border"
                            style="
                  box-sizing: border-box;
                  height: 100%;
                  padding: 0px;
                  border-top: 0px solid transparent;
                  border-left: 0px solid transparent;
                  border-right: 0px solid transparent;
                  border-bottom: 0px solid transparent;
                ">
                            <table id="u_content_heading_1" style="font-family: 'Raleway', sans-serif"
                                role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td style="
                                    overflow-wrap: break-word;
                                    word-break: break-word;
                                    padding: 0px 10px 0px 10px;
                                    font-family: 'Raleway', sans-serif;
                                    text-align: center;
                                  "
                                            align="center">
                                            <div style="display: inline-block; max-width: 150px;">
                                                <img style="width: 100%; display: block;"
                                                    src="https://firebasestorage.googleapis.com/v0/b/brooklyn-chat.appspot.com/o/invitation%20imgs%2Funnamed.png?alt=media&token=4b968f8f-f4d9-475c-b318-7f0e74553e7d"
                                                    alt="Company Logo" />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <table id="u_content_heading_2" style="font-family: 'Raleway', sans-serif"
                                role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td class="v-container-padding-padding"
                                            style="
                          overflow-wrap: break-word;
                          word-break: break-word;
                          padding: 0px 10px 10px 20px;
                          font-family: 'Raleway', sans-serif;
                        "
                                            align="center">
                                            <h1 class="v-text-align v-font-size"
                                                style="
                            margin: 0px;
                            line-height: 140%;
                            text-align: center;
                            word-wrap: break-word;
                            font-family: Epilogue;
                            font-size: 26px;
                            font-weight: 400;
                          ">
                                                Join Us in <strong>{{ $invitation->company->name }}</strong>
                                            </h1>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table id="u_content_image_1" style="font-family: 'Raleway', sans-serif" role="presentation"
                                cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td class="v-container-padding-padding"
                                            style="
                          overflow-wrap: break-word;
                          word-break: break-word;
                          padding: 0px 20px;
                          font-family: 'Raleway', sans-serif;
                        "
                                            align="left">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                                border="0">
                                                <tr>
                                                    <td class="v-text-align"
                                                        style="padding-right: 0px; padding-left: 0px" align="center">
                                                        <img align="center" border="0"
                                                            src="https://firebasestorage.googleapis.com/v0/b/brooklyn-chat.appspot.com/o/invitation%20imgs%2Fimage-1.png?alt=media&token=c2c2a817-7186-4f3d-af62-73302aca2b3a"
                                                            alt="image" title="image"
                                                            style="
                                  outline: none;
                                  text-decoration: none;
                                  -ms-interpolation-mode: bicubic;
                                  clear: both;
                                  display: inline-block !important;
                                  border: none;
                                  height: auto;
                                  float: none;
                                  width: 100%;
                                  max-width: 560px;
                                "
                                                            width="560" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table id="u_content_heading_4" style="font-family: 'Raleway', sans-serif"
                                role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td class="v-container-padding-padding"
                                            style="
                          overflow-wrap: break-word;
                          word-break: break-word;
                          padding: 20px 10px 5px 20px;
                          font-family: 'Raleway', sans-serif;
                        "
                                            align="left">
                                            <h1 class="v-text-align v-font-size"
                                                style="
                            margin: 0px;
                            line-height: 140%;
                            text-align: left;
                            word-wrap: break-word;
                            font-family: Epilogue;
                            font-size: 22px;
                            font-weight: 400;
                          ">
                                                <strong>{{ $invitation->inviter->name }} invited you to join</strong>
                                            </h1>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table id="u_content_text_2" style="font-family: 'Raleway', sans-serif" role="presentation"
                                cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td class="v-container-padding-padding"
                                            style="
                          overflow-wrap: break-word;
                          word-break: break-word;
                          padding: 0px 30px 10px 20px;
                          font-family: 'Raleway', sans-serif;
                        "
                                            align="left">
                                            <div class="v-text-align v-font-size"
                                                style="
                            font-size: 14px;
                            line-height: 140%;
                            text-align: left;
                            word-wrap: break-word;
                          ">
                                                <p style="line-height: 140%; margin: 0px">
                                                    We are excited to invite you to join us as we embark on the next
                                                    chapter of our journey. At
                                                    <strong>{{ $invitation->company->name }}</strong>, we are committed
                                                    to innovation, excellence, and building strong partnerships. This
                                                    event is a special opportunity to connect, collaborate, and share
                                                    our vision for the future. We look forward to celebrating with you
                                                    and creating new possibilities together.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table id="u_content_button_1" style="font-family: 'Raleway', sans-serif"
                                role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                <tbody>
                                    <tr>
                                        <td class="v-container-padding-padding"
                                            style="
                          overflow-wrap: break-word;
                          word-break: break-word;
                          padding: 10px 10px 50px 20px;
                          font-family: 'Raleway', sans-serif;
                        "
                                            align="left">
                                            <div class="v-text-align" align="center">
                                                <a href="https://www.1task.net/user-information#token={{ $invitation->token }}&expires_at={{ $invitation->expires_at->timestamp }}"
                                                    target="_blank" class="v-button v-font-size"
                                                    style="
                              box-sizing: border-box;
                              display: inline-block;
                              text-decoration: none;
                              text-size-adjust: none;
                              text-align: center;
                              color: rgb(255, 255, 255);
                              background: #A5C653;
                              border-radius: 4px;
                              width: auto;
                              max-width: 100%;
                              word-break: break-word;
                              overflow-wrap: break-word;
                              font-size: 14px;
                              line-height: inherit;
                            "><span
                                                        class="v-padding"
                                                        style="
                                display: block;
                                padding: 10px 20px;
                                line-height: 120%;
                              "><span
                                                            style="line-height: 16.8px">Collaborate with
                                                            Us</span></span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
