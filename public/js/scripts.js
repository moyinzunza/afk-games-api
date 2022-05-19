
'use strict';

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

function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const calculeteResources = function (thisElement) {
  $(`.btn.btn-create`).prop('disabled', false);
  let currQty = thisElement.val();
  let currMineral = thisElement.attr('data-mineral');
  let currCrystal = thisElement.attr('data-crystal');
  let currFuel = thisElement.attr('data-fuel')
  if (currQty > 0) {
    currMineral = thisElement.attr('data-mineral') * currQty;
    currCrystal = thisElement.attr('data-crystal') * currQty;
    currFuel = thisElement.attr('data-fuel') * currQty;
  }
  let currMineralg = $(`.universe__right__content__right__classification__item__content__number[data-mineral]`).attr('data-mineral');
  let currCrystalg = $(`.universe__right__content__right__classification__item__content__number[data-crystal]`).attr('data-crystal');
  let currFuelg = $(`.universe__right__content__right__classification__item__content__number[data-fuel]`).attr('data-fuel');
  if (currMineralg < currMineral ||
    currCrystalg < currCrystal ||
    currFuelg < currFuel) {
    $(`.btn.btn-create`).prop('disabled', true);
  }
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
  deleteCookie('cookie_current_module_id')
  deleteCookie('cookie_current_coords')
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
  deleteCookie('cookie_current_module_id')
  deleteCookie('cookie_current_coords')
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
  
  if (cookieExist.toString() !== `false`) {
    currDataAwait = await getData(`http://universe.artificialrevenge.com/api/home?module=${cookieExist}`, 'GET', '', headers, '');
  } else {
    currDataAwait = await getData('http://universe.artificialrevenge.com/api/home', 'GET', '', headers, '');
  }

  let defaultModuleID = currDataAwait.result.module.id;
  let defaultCoords = `${currDataAwait.result.module.position.galaxy}.${currDataAwait.result.module.position.solar_system}.${currDataAwait.result.module.position.planet}`;
  let cookieModuleID = getCookie("cookie_current_module_id");
  let cookieCoords = getCookie("cookie_current_coords");
  if (defaultModuleID === Number(cookieModuleID)) {
    document.cookie = `cookie_current_module_id=${cookieModuleID}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
    document.cookie = `cookie_current_coords=${cookieCoords}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
  } else {
    document.cookie = `cookie_current_module_id=${defaultModuleID}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
    document.cookie = `cookie_current_coords=${defaultCoords}; expires= Fri, 31 Dec 9999 23:59:59 GMT; path=/`;
  }
  let cookieModuleIDUpdate = getCookie("cookie_current_module_id");
  setTimeout(function(){
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
                    data-generate-qty-minute="${Number(currDataAwait.result.module.resources[k].generate_qty_minute)}"
                    data-${currDataAwait.result.module.resources[k].name.toLocaleUpperCase()}="${Number(currDataAwait.result.module.resources[k].qty)}"
                    data-qty="${Number(currDataAwait.result.module.resources[k].qty)}"
                    >
                    ${numberWithCommas(Number(currDataAwait.result.module.resources[k].qty))}
                  </span>
                  <span>${currDataAwait.result.module.resources[k].name}</span>
              </div>
            </div>
          `
      $('.universe__right__content__right__classification__items').append(template)
    });
  }, 100);
  
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
            <div class="universe__right__content__right__planets__item" data-id="${currDataAwait.result[k].id}" 
            data-coords="${currDataAwait.result[k].position.galaxy}.${currDataAwait.result[k].position.solar_system}.${currDataAwait.result[k].position.planet}">
              <div class="universe__right__content__right__planets__item__image" style="background-image: url(/images/icon_3.svg);"></div>
              <div class="universe__right__content__right__planets__item__content">
                <span>${currDataAwait.result[k].name}</span>
                <span >(${currDataAwait.result[k].position.galaxy}.${currDataAwait.result[k].position.solar_system}.${currDataAwait.result[k].position.planet})</span>
              </div>
            </div>
          `
    $('.universe__right__content__right__planets__items').append(template)
  });
}

const getModuleInfo = async function (currUrl) {
  let cookieModuleIDUpdate = getCookie("cookie_current_module_id");
  let currToken = getCookie("access_token");
  $('.universe__right__content__left__container__third__section.resources').removeClass('active');
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken}`
  }
  let currDataAwait = await getData(`http://universe.artificialrevenge.com/api/module/${cookieModuleIDUpdate}/${currUrl}`, 'GET', '', headers, '')
  $('.universe__right__content__left__container__first__content__img').css('background-image', `url(${currDataAwait.result.image})`);
  $('.universe__right__content__left__container__second.resources').html('');

  Object.keys(currDataAwait.result.items).forEach(function (k) {
    let template = `
      <div class="universe__right__content__left__container__second__img ${currDataAwait.result.items[k].all_conditions_fullfilled === false ? 'opacity-this-section' : ''}" 
        data-level-id="${currDataAwait.result.items[k].id}"
        data-json='${JSON.stringify(currDataAwait.result.items[k])}'
        
        style="background-image: url(${currDataAwait.result.items[k].image});">
        <p>
        <span>${currDataAwait.result.items[k].level || currDataAwait.result.items[k].level === 0 ? currDataAwait.result.items[k].level : currDataAwait.result.items[k].qty}</span>  ${currDataAwait.result.items[k].name}
        </p>
      </div>
      `
    $('.universe__right__content__left__container__second.resources').append(template)
  });
  $('.universe__right__content__left__container__third__section__content__corners__buildings').html('');
  if (currDataAwait.result.items_line) {
    Object.keys(currDataAwait.result.items_line).forEach(function (k) {
      let templateNextLevel = currDataAwait.result.items_line[k].next_level || currDataAwait.result.items_line[k].next_level === 0 ? `Level (<span class="green">${currDataAwait.result.items_line[k].next_level}</span>)` : `(<span class="green">${currDataAwait.result.items_line[k].qty}</span>)`
      $(`.universe__right__content__left__container__second__img[data-level-id="${currDataAwait.result.items_line[k].id}"]`).attr('isUpgraded', '1');
      if (currDataAwait.result.items_line[k].qty || currDataAwait.result.items_line[k].qty === 0) {
        $(`.universe__right__content__left__container__second__img[data-level-id="${currDataAwait.result.items_line[k].id}"]`).removeAttr('isUpgraded');
      }
      let templateBuilding = `
         <div data-leve-id="${currDataAwait.result.items_line[k].id}" >
          <img src="${currDataAwait.result.items_line[k].image}" alt="Build" />
          <div>
            <p>
              <b>${currDataAwait.result.items_line[k].name} ${templateNextLevel}</b><br />
              <span class="span-counter-upgraded" 
                id="clock-${currDataAwait.result.items_line[k].id_line}" 
                data-id="${currDataAwait.result.items_line[k].id_line}" 
                data-date-init="${currDataAwait.result.items_line[k].date_init}"
                data-date-finish="${currDataAwait.result.items_line[k].date_finish}"
                ></span>
            </p>
          <div>
         </div>
      `;
      $('.universe__right__content__left__container__third__section__content__corners__buildings').append(templateBuilding);

    });


  }
  if (currDataAwait.result.items_line) {
    if (currDataAwait.result.items_line.length > 0) {
      $('.universe__right__content__left__container__third__section.resources').addClass('active');
      let timeFinish = $('.universe__right__content__left__container__third__section__content__corners__buildings > div:last-child .span-counter-upgraded').attr('data-date-finish');
      let timeFinishSEconds = Number(timeFinish) * 1000;
      let timeFinishDate = new Date(timeFinishSEconds);
      $(`.universe__right__content__left__container__third__section__title`).append('<span id="clock-time-header" class="universe__right__content__left__container__third__section__title__time"></span>');
      $(`#clock-time-header`).countdown(timeFinishDate, function (event) {
        if (event.strftime('%D') == '00') {
          $(this).html(`Total time ${event.strftime('%H:%M:%S')}`);
        } else {
          $(this).html(`Total time ${event.strftime('%D Days %H:%M:%S')}`);
        }
      });
    }
  }
  $(".span-counter-upgraded").each(function (index) {
    let dataFinishUnixTimestamp = $(this).attr('data-date-finish');
    let dataFinishMilliseconds = dataFinishUnixTimestamp * 1000;
    let currDateFinish = new Date(dataFinishMilliseconds);
    let currID = $(this).attr('data-id');
    $(`#clock-${currID}`).countdown(currDateFinish, function (event) {
      if (event.strftime('%D') == '00') {
        $(this).html(event.strftime('%H:%M:%S'));
      } else {
        $(this).html(event.strftime('%D Days %H:%M:%S'));
      }
      if (event.strftime('%D') == '00' &&
        event.strftime('%H') == '00' &&
        event.strftime('%M') == '00' &&
        event.strftime('%S') == '00') {
        setTimeout(function () {
          location.reload();
        }, 5000);
      }
    });
  });
}

const updateResorces60seconds = function () {
  setInterval(function () {
    $(".universe__right__content__right__classification__item").each(function (index) {
      let currentItemNumber = Number($(this).find('.universe__right__content__right__classification__item__content__number').attr('data-qty'));
      //let currentItemDataBuildingLevel = $(this).find('.universe__right__content__right__classification__item__content__number').attr('data-building-level');
      let currentItemDataGenerateQtyMinute = Number($(this).find('.universe__right__content__right__classification__item__content__number').attr('data-generate-qty-minute'));
      $(this).find('.universe__right__content__right__classification__item__content__number').text(numberWithCommas(currentItemNumber + currentItemDataGenerateQtyMinute));
    });
  }, 60000);
}

const closeErrorsContent = function (thisElement) {
  $(thisElement).parent().hide();
  $(thisElement).parent().html('');
}

const showDetails = function (thisElement) {
  if ($(thisElement).attr('data-json')) {
    $('.universe__right__content__left__container__second__img').removeClass('active');
    $(thisElement).addClass('active')
    let currJson = JSON.parse($(thisElement).attr('data-json'));
    console.log(currJson);
    $('.universe__right__content__left__container__first__popup').html('');
    $('.universe__right__content__left__container__first__popup').addClass('active');
    let templateNextLevel = currJson.level || currJson.level === 0 ? `Next level (<span class="green">${currJson.level + 1}</span>)` : ''
    let templateBTN = currJson.level || currJson.level === 0 ?
      `<button data-id="${currJson.id}" class="btn btn-upgrade"> UPGRADE </button>` :
      `<div class="universe-input universe-input-quantity">
        <label for="login-email">Quantity</label>
        <input type="number" id="create-quantity"
        data-mineral="${currJson.price_time.mineral}"
        data-crystal="${currJson.price_time.crystal}"
        data-fuel="${currJson.price_time.fuel}">
      </div> 
      <button data-id="${currJson.id}" class="btn btn-upgrade btn-create"> CREATE </button>`
    let template = `
    <img src="/images/cancel.png" alt="x"/>
    <div>
      <img src="${currJson.image}" alt="image"/>
      <div>
        <p class="name"><b>${currJson.name} (<span class="green">${currJson.level || currJson.level === 0 ? currJson.level : currJson.qty}</span>)</b></p>
        <hr />
        <p><b> ${templateNextLevel} </b ></p >
        <span>
          Time: <b>${currJson.price_time.time_minutes} minutes</b><br />
          Mineral: <b>${numberWithCommas(currJson.price_time.mineral)}</b> <br />
          Cristal: <b>${numberWithCommas(currJson.price_time.crystal)}</b> <br />
          Fuel: <b>${numberWithCommas(currJson.price_time.fuel)}</b>
        </span>
        <br /><br />
        ${templateBTN}
      </div >
    </div >
  `
    $('.universe__right__content__left__container__first__popup').html(template);
    let currIsUpgraded = $(thisElement).attr('isupgraded')
    if (currIsUpgraded === '1') {
      $(`.btn-upgrade[data-id="${currJson.id}"]`).prop('disabled', true);
      $(`.btn-create[data-id="${currJson.id}"]`).prop('disabled', true);
    }
    let currMineral = $(`.universe__right__content__right__classification__item__content__number[data-mineral]`).attr('data-mineral');
    let currCrystal = $(`.universe__right__content__right__classification__item__content__number[data-crystal]`).attr('data-crystal');
    let currFuel = $(`.universe__right__content__right__classification__item__content__number[data-fuel]`).attr('data-fuel');
    if (currMineral < currJson.price_time.mineral ||
      currCrystal < currJson.price_time.crystal ||
      currFuel < currJson.price_time.fuel) {
      $(`.btn-upgrade[data-id="${currJson.id}"]`).prop('disabled', true);
      $(`.btn-create[data-id="${currJson.id}"]`).prop('disabled', true);
      $('.universe-input-quantity').remove();
    }
    if (currJson.all_conditions_fullfilled === false) {
      $(`.btn-upgrade[data-id="${currJson.id}"]`).prop('disabled', true);
      $(`.btn-create[data-id="${currJson.id}"]`).prop('disabled', true);
      let template = `
        <div class="universe__right__content__left__container__first__popup__resources">
          <p class="universe__right__content__left__container__first__popup__resources__title">
            Require
          </p> 
          <div class="universe__right__content__left__container__first__popup__resources__list">
          </div>
        </div>`;
      $(`.btn-upgrade[data-id="${currJson.id}"], .btn-create[data-id="${currJson.id}"]`).after(template)
      Object.keys(currJson.require).forEach(function (k) {
        if (!currJson.require[k].fulfilled) {
          let resourceTemplate = `
            <div class="universe__right__content__left__container__first__popup__resources__list__resource">
              <div style="background-image: url(${currJson.require[k].image})"></div>
              <p>
                <span class="name"><span>${currJson.require[k].name}</span> (${currJson.require[k].level}) </span>
                <!--<span class="type">Type: <span>${currJson.require[k].type}</span></span>-->
              </p>
            </div>
          `;
          $('.universe__right__content__left__container__first__popup__resources__list').append(resourceTemplate);
        }
      });
      $('.universe-input-quantity').remove();
    }
  }
}

const upgrade = async function (currID, thisElement) {
  let cookieModuleIDUpdate = getCookie("cookie_current_module_id");
  let currToken = getCookie("access_token");
  let getCurrUrl = window.location.pathname.replace('/', '');
  let data = {
    "id": currID
  }
  if (thisElement.hasClass('btn-create')) {
    let currQuantity = Number($('#create-quantity').val())
    data = {
      "id": currID,
      "qty": currQuantity
    }
    if (currQuantity === 0 || currQuantity === '' || currQuantity === NaN || currQuantity === null) {
      return false;
    }
  }
  let headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${currToken} `
  }


  let currDataAwait = await getData(`http://universe.artificialrevenge.com/api/module/${cookieModuleIDUpdate}/${getCurrUrl}`, 'POST', data, headers, '');
  thisElement.prop('disabled', true);
  location.reload();
}

const hideDetails = function () {
  $('.universe__right__content__left__container__first__popup').removeClass('active');
  $('.universe__right__content__left__container__second__img').removeClass('active');
  $('.universe__right__content__left__container__first__popup').html('');
}

const getMaps = async function (typeEvent = `load`) {

  if($(`.universe__right__content__left__container__first__content__ranges`).length > 0){
    let cookieCoords = getCookie("cookie_current_coords");
    let currModuleId = getCookie("cookie_current_module_id"); 
    const arrCoords = cookieCoords.split(`.`);
    let currToken = getCookie("access_token");
    let headers = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${currToken}`
    }
    let currDataAwait;
    if(typeEvent === `update`){
      console.log(`updates values cords`)
      let position_z = Number($(".galaxiInput").val());
      let position_y = Number($(".planetInput").val()); 
      position_z = position_z === 0 ? 1 : position_z;
      position_y = position_y === 0 ? 1 : position_y;
      console.log(position_y, position_z)
      currDataAwait = await getData(`http://universe.artificialrevenge.com/api/module/${currModuleId}/map?position_y=${position_y}&position_z=${position_z}`, 'GET', '', headers, '');
    }else{
      currDataAwait = await getData(`http://universe.artificialrevenge.com/api/module/${currModuleId}/map?position_y=${arrCoords[1]}&position_z=${arrCoords[0]}`, 'GET', '', headers, '');
      $(`.galaxiRange, .galaxiInput`).val(arrCoords[0]);
      $(`.planetRange, .planetInput`).val(arrCoords[1]);
    }

    let currTableBody = $(`.universe__right__content__left__container__table__content > table > tbody`);

    currTableBody.html(``);

    // currDataAwait.result.forEach(function(module, index){
    //   module.positionPlanet = module.position.planet;
    // })

    let currArrayModules = [];
    
    for(let i = 1; i <= 11; i++){
      let existPlanetPosition = currDataAwait.result.filter(function(objetPlanet){
        return objetPlanet.positionPlanet === i;
      })
      if(existPlanetPosition.length > 0){
        currArrayModules.push(existPlanetPosition)
      }else{
        currArrayModules.push({positionPlanet: i})
      }
    }
    console.log(currArrayModules)
    currArrayModules.forEach(function(module, index){
      let currTemplateTr = `
        <tr>
          <td class="fullbg">${index + 1}</td>
          <td class="${module?.[0]?.name ? `` : `notbg`}">${module?.[0]?.name ? module?.[0]?.name : ``}</td>
          <td>${module?.[0]?.name ? module?.[0]?.name : `iconos`} </td>
          <td class="${module?.[0]?.user_data.username ? `` : `notbg`}">${module?.[0]?.user_data.username ? module?.[0]?.user_data.username : ''}</td>
          <td class="notbg">acciones</td>
        </tr>
      `;
      currTableBody.append(currTemplateTr)
    })  

  }
  
}

const validateMaxMinNumber = function(element, max, min){
  let currValue = $(element).val()
  let v = parseInt(currValue);
  if (v < min) $(element).val(min);
  if (v > max) $(element).val(max);
}

$(function () {

  getHome();

  updateResorces60seconds();

  getModules();

  getMaps();

  if ($('.universe__right__content__left__container').hasClass('resources')) {
    let getCurrUrl = window.location.pathname.replace('/', '');
    getModuleInfo(getCurrUrl);
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
    let currCoords = $(this).attr('data-coords');
    deleteCookie('cookie_current_module_id');
    deleteCookie('cookie_current_coords');
    setCookie('cookie_current_module_id', currId, 3600);
    setCookie('cookie_current_coords', currCoords, 3600);
    $(`.universe__right__content__right__planets__item`).removeClass('active');
    $(this).addClass('active');
    getHome();
    if ($('.universe__right__content__left__container').hasClass('resources')) {
      let getCurrUrl = window.location.pathname.replace('/', '');
      getModuleInfo(getCurrUrl);
    }
    hideDetails();
    getMaps();
  })

  $(document).on('click', '.universe__right__content__left__container__second__img', function () {
    showDetails(this);
  })

  $(document).on('click', '.universe__right__content__left__container__first__popup > img', function () {
    hideDetails();
  })

  $(document).on('click', '.btn-upgrade', function () {
    let currID = $(this).attr('data-id');
    upgrade(currID, $(this));
  })

  $(document).on('keyup', '#create-quantity', function () {
    calculeteResources($(this))
  })

  $(document).on('change', '#create-quantity', function () {
    calculeteResources($(this))
  })

  $(document).on('click', '.universe__right__content__left__container__first__content__ranges > .btn', function (e) {
    e.preventDefault();
    getMaps(`update`)
  })

  

})