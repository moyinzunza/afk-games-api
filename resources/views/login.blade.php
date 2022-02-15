<!DOCTYPE html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Universe</title>
  <meta charset="utf-8" />
  <link href="/css/styles.scss" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="universe-account">
    <div class="universe-account-login">
      <img class="corner-left-top" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-right-top" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-right-bottom" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-left-bottom" src="/images/icon_c.svg" alt="corner" />
      <p>
        Universe login
      </p>
      <div class="universe-input">
        <label for="login-email">Email</label>
        <input type="text" id="login-email">
      </div>
      <div class="universe-input">
        <label for="login-password">Password</label>
        <input type="password" id="login-password">
      </div>
      <div class="universe-input-checkbox">
        <input type="checkbox" id="login-remember-me" value="second_checkbox"> <label for="login-remember-m">Remember me</label>
      </div>
      <div class="universe-account-errors">
      </div>
      <button class="btn" id="btn-login">
        Login
      </button>
      <a class="accont-other-container register">Register</a>
      <br />
    </div>
    <div class="universe-account-register">
      <img class="corner-left-top" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-right-top" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-right-bottom" src="/images/icon_c.svg" alt="corner" />
      <img class="corner-left-bottom" src="/images/icon_c.svg" alt="corner" />
      <p>
        Universe register
      </p>
      <div class="universe-input">
        <label for="register-name">Name</label>
        <input type="text" id="register-name">
      </div>
      <div class="universe-input">
        <label for="register-email">Email</label>
        <input type="text" id="register-email">
      </div>
      <div class="universe-input">
        <label for="register-username">Username</label>
        <input type="text" id="register-username">
      </div>
      <div class="universe-input">
        <label for="register-password">Password</label>
        <input type="password" id="register-password">
      </div>
      <div class="universe-input">
        <label for="register-password-1">Confirm password</label>
        <input type="password" id="register-password-1">
      </div>
      <div class="universe-input">
        <label for="register-referred">Referred by username</label>
        <input type="text" id="register-referred">
      </div>
      <div class="universe-account-errors">
      </div>
      <button class="btn" id="btn-register">
        Register
      </button>
      <a class="accont-other-container login">Login</a>
      <br />
    </div>
  </div>
  <script src="/js/jquery.js"></script>
  <script src="/js/scripts.js"></script>
  <script>
    const showOtherContainerAccount = function(thisElement) {
      if ($(thisElement).hasClass('register')) {
        $('.universe-account-login').hide()
        $('.universe-account-register').fadeIn()
      } else if ($(thisElement).hasClass('login')) {
        $('.universe-account-login').fadeIn()
        $('.universe-account-register').hide()
      }
    }
    $(function() {
      $(document).on('click', '.accont-other-container', function() {
        showOtherContainerAccount(this)
      })
    })
  </script>

</body>

</html>