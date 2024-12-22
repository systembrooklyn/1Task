<!DOCTYPE html>
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