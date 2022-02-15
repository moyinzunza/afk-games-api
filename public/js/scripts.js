let removeSpaces = /^\s+$/;

const getData = function (paramUrl = '', paramMtdType = '', paramData = '', paramHeaders = '', currBtn = '', paramEvent = '') {
  let lastTextBtn = currBtn != '' ? currBtn.text() : ''
  currBtn != '' ? currBtn.text('Sending...') : ''
  const getDataAsync = async function () {
    let response;
    if (paramMtdType === "POST") {
      response = await fetch(paramUrl, {
        method: paramMtdType,
        headers: paramHeaders,
        body: JSON.stringify(paramData)
      })
    } else {
      response = await fetch(paramUrl, {
        method: paramMtdType,
        headers: paramHeaders
      })
    }
    const data = await response.json();
    currBtn != '' ? currBtn.prev().html('') : ''
    if (data.status.statusCode === 201 || data.status.statusCode === 200) {
      if (paramEvent === 'register') {
        currBtn.prev().append(`<img src="/images/close.png" class="close-errors" />`);
        currBtn.prev().append(`<p class="success">${data.status.message}</p>`);
        $('.universe-account-register input').val('');
        currBtn.text(`${lastTextBtn}`)
        currBtn.prev().fadeIn();
      } else if (paramEvent === 'login') {
        document.cookie = `access_token=${data.result.access_token}; expires=${new Date(data.result.expires_at)}; path=/`;
        window.location.href = "/"
        currBtn != '' ? currBtn.text(`${lastTextBtn}`) : ''
      } else if (paramEvent === 'logout') {
        window.location.href = "/login"
      } else if (paramEvent === 'gethome') {
        $('.universe__right__content__right__classification__items').html('')
        Object.keys(data.result.module.resources).forEach(function (k) {
          let template = `
            <div class="universe__right__content__right__classification__item">
              <div class="universe__right__content__right__classification__item__image" style="background-image: url(/images/imagedemo.jpg);">
              </div>
              <div class="universe__right__content__right__classification__item__content">
                  <span class="universe__right__content__right__classification__item__content__number" 
                    data-building-level="${data.result.module.resources[k].building_level}" 
                    data-generate-qty-minute="${data.result.module.resources[k].generate_qty_minute}">${data.result.module.resources[k].qty}</span>
                  <span>${k}</span>
              </div>
            </div>
          `
          $('.universe__right__content__right__classification__items').append(template)
          //console.log(k, data.result.module.resources[k]);
        });
      } else if (paramEvent === 'getmodules') {
        $('.universe__right__content__right__planets__items').html('')
        Object.keys(data.result).forEach(function (k) {
          //console.log(k, data.result[k]);
          let template = `
            <div class="universe__right__content__right__planets__item" data-id="${data.result[k].id}">
              <div class="universe__right__content__right__planets__item__image" style="background-image: url(/images/icon_3.svg);"></div>
              <div class="universe__right__content__right__planets__item__content">
                <span>${data.result[k].name}</span>
                <span>(${data.result[k].position.galaxy}.${data.result[k].position.planet}.${data.result[k].position.solar_system})</span>
              </div>
            </div>
          `
          $('.universe__right__content__right__planets__items').append(template)
        });
      }
    } else {
      currBtn != '' ? currBtn.prev().append(`<img src="/images/close.png" class="close-errors" />`) : ''
      currBtn != '' ? currBtn.prev().append(`<p class="error">${data.status.message}</p>`) : ''
      if (data.result) {
        Object.keys(data.result.errors).forEach(function (k) {
          currBtn != '' ? currBtn.prev().append(`<p class="error">x ${data.result.errors[k]}</p>`) : ''
          //console.log(k, data.result.errors[k]);
        });
      }
      currBtn != '' ? currBtn.prev().fadeIn() : ''
      currBtn != '' ? currBtn.text(`${lastTextBtn}`) : ''
      if (data.status.message === 'Unauthenticated') {
        deleteCookie('access_token')
        if (location.pathname != '/login') window.location.href = "/login"
      }
    }
  }
  getDataAsync();
}

function getCookie(name) {
  var pattern = RegExp(name + "=.[^;]*")
  var matched = document.cookie.match(pattern)
  if (matched) {
    var cookie = matched[0].split('=')
    return cookie[1]
  }
  return false
}

function deleteCookie(name) {
  document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

const register = function (thisElement) {
  if ($('#register-password').val() != $('#register-password-1').val() || $('#register-password').val() === '' || removeSpaces.test($('#register-password').val())) {
    $(thisElement).prev().fadeIn();
    $(thisElement).prev().html('');
    $(thisElement).prev().append(`<img src="/images/close.png" class="close-errors" />`);
    $(thisElement).prev().append(`<p class="error">x Passwords do not match</p>`);
    return false;
  }
  let data = {
    name: $('#register-name').val(),
    email: $('#register-email').val(),
    password: $('#register-password').val(),
    username: $('#register-username').val(),
    referred_by_username: $('#register-referred').val()
  }
  let headers = {
    'Content-Type': 'application/json'
  }
  getData('http://universe.artificialrevenge.com/api/signup', 'POST', data, headers, $(thisElement), 'register')
}

const login = function (thisElement) {
  let rememberMe = false
  if ($('#login-remember-me').is(':checked')) {
    rememberMe = true
  }
  let data = {
    email: $('#login-email').val(),
    password: $('#login-password').val(),
    remember_me: rememberMe
  }
  let headers = {
    'Content-Type': 'application/json'
  }
  getData('http://universe.artificialrevenge.com/api/login', 'POST', data, headers, $(thisElement), 'login')
}

const logout = function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  getData('http://universe.artificialrevenge.com/api/logout', 'POST', '', headers, '', 'logout')
  deleteCookie('access_token')
}

const getHome = function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  getData('http://universe.artificialrevenge.com/api/home', 'GET', '', headers, '', 'gethome')
}

const getModules = function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  getData('http://universe.artificialrevenge.com/api/get_modules', 'GET', '', headers, '', 'getmodules')
}


const updateResorces60seconds = function () {
  setInterval(function () {
    $(".universe__right__content__right__classification__item").each(function (index) {
      let currentItemText = $(this).find('.universe__right__content__right__classification__item__content__number').text();
      let currentItemDataBuildingLevel = $(this).find('.universe__right__content__right__classification__item__content__number').attr('data-building-level');
      let currentItemDataGenerateQtyMinute = $(this).find('.universe__right__content__right__classification__item__content__number').attr('data-generate-qty-minute');
      $(this).find('.universe__right__content__right__classification__item__content__number').text(Number(currentItemText) + Number(currentItemDataGenerateQtyMinute));
    });
  }, 60000);
}

const closeErrorsContent = function (thisElement) {
  $(thisElement).parent().hide();
  $(thisElement).parent().html('');
}

$(function () {

  getHome();

  updateResorces60seconds();

  getModules();

  $(document).on('click', '#btn-login', function () {
    login(this);
  })

  $(document).on('click', '#btn-register', function () {
    register(this);
  })

  $(document).on('click', '.logout', function (e) {
    e.preventDefault();
    logout();
  })

  $(document).on('click', '.universe-account-errors .close-errors', function () {
    closeErrorsContent(this);
  })

})