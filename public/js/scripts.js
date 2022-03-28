let removeSpaces = /^\s+$/;

const getData = function (paramUrl = '', paramMtdType = '', paramData = '', paramHeaders = '', currBtn = '') {
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
      return data;
    } else {
      currBtn != '' ? currBtn.prev().append(`<img src="/images/close.png" class="close-errors" />`) : ''
      currBtn != '' ? currBtn.prev().append(`<p class="error">${data.status.message}</p>`) : ''
      if (data.result) {
        Object.keys(data.result.errors).forEach(function (k) {
          currBtn != '' ? currBtn.prev().append(`<p class="error">x ${data.result.errors[k]}</p>`) : ''
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
  return getDataAsync();
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

function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  let expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

const register = async function (thisElement) {
  let lastTextBtn = $(thisElement) != '' ? $(thisElement).text() : ''
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
  let currDataAwait = await getData('http://universe.artificialrevenge.com/api/signup', 'POST', data, headers, $(thisElement))
  $(thisElement).prev().append(`<img src="/images/close.png" class="close-errors" />`);
  $(thisElement).prev().append(`<p class="success">${currDataAwait.status.message}</p>`);
  $('.universe-account-register input').val('');
  $(thisElement).text(`${lastTextBtn}`)
  $(thisElement).prev().fadeIn();

}

const login = async function (thisElement) {
  let rememberMe = false
  let lastTextBtn = $(thisElement) != '' ? $(thisElement).text() : ''
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
  let currDataAwait = await getData('http://universe.artificialrevenge.com/api/login', 'POST', data, headers, $(thisElement))
  document.cookie = `access_token=${currDataAwait.result.access_token}; expires=${new Date(currDataAwait.result.expires_at)}; path=/`;
  window.location.href = "/"
  $(thisElement) != '' ? $(thisElement).text(`${lastTextBtn}`) : ''
}

const logout = async function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  let currDataAwait = await getData('http://universe.artificialrevenge.com/api/logout', 'POST', '', headers, '')
  deleteCookie('access_token')
  window.location.href = "/login"
}

const getHome = async function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  let cookieExist = getCookie("cookie_current_module_id");
  let currDataAwait;
  if (cookieExist) {
    currDataAwait = await getData(`http://universe.artificialrevenge.com/api/home?module=${cookieExist}`, 'GET', '', headers, '');
  } else {
    currDataAwait = await getData('http://universe.artificialrevenge.com/api/home', 'GET', '', headers, '');
  }
  let defaultModuleID = currDataAwait.result.module.id;
  let cookieModuleID = getCookie("cookie_current_module_id");
  if (defaultModuleID === Number(cookieModuleID)) {
    document.cookie = `cookie_current_module_id=${defaultModuleID}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
  } else {
    document.cookie = `cookie_current_module_id=${cookieModuleID}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
  }
  let cookieModuleIDUpdate = getCookie("cookie_current_module_id");
  $('.universe__right__content__left__container__first__content__text').text(currDataAwait.result.module.name);
  $(`.universe__right__content__right__planets__item`).removeClass('active');
  $(`.universe__right__content__right__planets__item[data-id=${cookieModuleIDUpdate}]`).addClass('active');
  $('.universe__right__content__right__classification__items').html('')
  Object.keys(currDataAwait.result.module.resources).forEach(function (k) {
    let template = `
          <div class="universe__right__content__right__classification__item">
            <div class="universe__right__content__right__classification__item__image" style="background-image: url(${currDataAwait.result.module.resources[k].image});">
            </div>
            <div class="universe__right__content__right__classification__item__content">
                <span class="universe__right__content__right__classification__item__content__number" 
                  data-building-level="${currDataAwait.result.module.resources[k].building_level}" 
                  data-generate-qty-minute="${currDataAwait.result.module.resources[k].generate_qty_minute}">${currDataAwait.result.module.resources[k].qty}</span>
                <span>${k}</span>
            </div>
          </div>
        `
    $('.universe__right__content__right__classification__items').append(template)
  });
}

const getModules = async function () {
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  let currDataAwait = await getData('http://universe.artificialrevenge.com/api/get_modules', 'GET', '', headers, '')
  $('.universe__right__content__right__planets__items').html('')
  Object.keys(currDataAwait.result).forEach(function (k) {
    let template = `
            <div class="universe__right__content__right__planets__item" data-id="${currDataAwait.result[k].id}">
              <div class="universe__right__content__right__planets__item__image" style="background-image: url(/images/icon_3.svg);"></div>
              <div class="universe__right__content__right__planets__item__content">
                <span>${currDataAwait.result[k].name}</span>
                <span>(${currDataAwait.result[k].position.galaxy}.${currDataAwait.result[k].position.planet}.${currDataAwait.result[k].position.solar_system})</span>
              </div>
            </div>
          `
    $('.universe__right__content__right__planets__items').append(template)
  });
}

const getResources = async function () {
  let cookieModuleIDUpdate = getCookie("cookie_current_module_id");
  let currToken = getCookie("access_token");
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  let currDataAwait = await getData(`http://universe.artificialrevenge.com/api/module/${cookieModuleIDUpdate}/resources`, 'GET', '', headers, '')
  $('.universe__right__content__left__container__second.resources').html('');
  Object.keys(currDataAwait.result.levels).forEach(function (k) {
    let template = `
      <div class="universe__right__content__left__container__second__img" 
        data-level-id="${currDataAwait.result.levels[k].id}"
        data-json='${JSON.stringify(currDataAwait.result.levels[k])}'
        style="background-image: url(${currDataAwait.result.levels[k].image});">
        <p>
        <span>${currDataAwait.result.levels[k].level}</span>  ${currDataAwait.result.levels[k].name}
        </p>
      </div>
      `
    $('.universe__right__content__left__container__second.resources').append(template)
  });
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

const showDetailsResources = function (thisElement) {
  if ($(thisElement).attr('data-json')) {
    $('.universe__right__content__left__container__second__img').removeClass('active');
    $(thisElement).addClass('active')
    let currJson = JSON.parse($(thisElement).attr('data-json'));
    $('.universe__right__content__left__container__first__popup').html('');
    $('.universe__right__content__left__container__first__popup').addClass('active');
    let template = `
    <img src="/images/cancel.png" alt="x"/>
    <div>
       Hola
    </div>
  `
    $('.universe__right__content__left__container__first__popup').html(template);
    console.log(currJson);
  }
}

const hideDetailsResources = function () {
  $('.universe__right__content__left__container__first__popup').removeClass('active');
  $('.universe__right__content__left__container__second__img').removeClass('active');
  $('.universe__right__content__left__container__first__popup').html('');
}

$(function () {

  getHome();

  updateResorces60seconds();

  getModules();

  if ($('.universe__right__content__left__container').hasClass('resources')) {
    getResources();
  }

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

  $(document).on('click', '.universe__right__content__right__planets__item', function () {
    let currId = $(this).attr('data-id');
    deleteCookie('cookie_current_module_id');
    setCookie('cookie_current_module_id', currId, 3600);
    $(`.universe__right__content__right__planets__item`).removeClass('active');
    $(this).addClass('active');
    getHome();
    getResources();
    hideDetailsResources();
  })

  $(document).on('click', '.universe__right__content__left__container__second__img', function () {
    showDetailsResources(this);
  })

  $(document).on('click', '.universe__right__content__left__container__first__popup > img', function () {
    hideDetailsResources();
  })

})