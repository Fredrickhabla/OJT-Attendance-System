<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Coordinator Profile</title>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background-color: #f0f2f5;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .card {
      width: 400px;
      background-color: white;
      border-radius: 24px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
      position: relative;
      overflow: hidden;
    }

    .card-header {
      text-align: center;
      padding: 20px;
      font-size: 20px;
      font-weight: 500;
      border-bottom: 1px solid #eee;
    }

    .card-content {
      padding: 20px;
    }

    .avatar-wrapper {
      position: relative;
      display: flex;
      justify-content: center;
      margin-bottom: 24px;
    }

    .avatar {
      width: 104px;
      height: 104px;
      background-color: #e2e8f0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .avatar-fallback {
      background-color: #d1d5db;
      border-radius: 50%;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .icon {
      width: 48px;
      height: 48px;
      color: #9ca3af;
    }

    .insert-photo-btn {
  position: absolute;
  top: 50%;
  right: 30px;
  transform: translateY(-50%);
  background-color: #047857;
  color: white;
  font-size: 12px;
  padding: 6px 10px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
}



    .insert-photo-btn:hover {
      background-color: #065f46;
      
    }

    .form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-size: 14px;
      margin-bottom: 4px;
    }

    input {
      border: 1px solid #047857;
      border-radius: 999px;
      padding: 8px 12px;
      outline: none;
    }

    input:focus {
      border-color: #047857;
      box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.3);
    }

    .form-actions {
      display: flex;
      justify-content: center;
      padding-top: 10px;
    }

    .save-btn {
      background-color: #047857;
      color: white;
      border: none;
      border-radius: 999px;
      padding: 10px 32px;
      cursor: pointer;
      width: 128px;
    }

    .save-btn:hover {
      background-color: #065f46;
    }

    .h2coord{
        font-size: 24px;
        color: #065f46;
        margin: 0;
        text-align: center;

    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        <h2 class="h2coord">Coordinator Profile</h2>
      </div>
      <div class="card-content">
        <div class="avatar-wrapper">
          <div class="avatar">
            <div class="avatar-fallback">
              <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0M4.5 20.25h15"/>
              </svg>
              
            </div>
            
          </div>
          <button class="insert-photo-btn">Insert Photo</button>
        </div>

        <form class="form">
          <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" />
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <input type="text" id="position" />
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" />
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" />
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
