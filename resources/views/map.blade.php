@extends('layouts.game')

@section('styles')

@endsection

@section('content')
<div class="universe__right__content__left__container">
<div class="universe__right__content__left__container__first">
    <img src="/images/cuadro-chico.png" alt="bg" />
    
    <div class="universe__right__content__left__container__first__content" style="z-index: 1;">
      <div class="universe__right__content__left__container__first__content__ranges">
        <img data-tippy-content="Universe" class="tippy" src="/images/galaxy2.png" alt="Galaxy"/>
        <form>
          <input type="range"  class="range galaxiRange" name="galaxiRange" min="1" max="5" value="1" oninput="this.form.galaxiInput.value=this.value" />
          <input type="number" name="galaxiInput" class="galaxiInput" min="1" max="5" value="1" onchange="validateMaxMinNumber(`.galaxiInput`, 5, 1)" oninput="this.form.galaxiRange.value=this.value" />
        </form>
      </div>
      <div class="universe__right__content__left__container__first__content__ranges">
        <img data-tippy-content="Solar system" class="tippy" src="/images/sistema-solar.png" alt="Planet"/>
        <form>
          <input type="range" class="range planetRange" name="planetRange" min="1" max="250" value="1"  oninput="this.form.planetInput.value=this.value" />
          <input type="number" name="planetInput" class="planetInput" min="1" max="250" value="1" onchange="validateMaxMinNumber(`.planetInput`, 250, 1)" oninput="this.form.planetRange.value=this.value" />
        </form>
      </div>
      <div class="universe__right__content__left__container__first__content__ranges">
        <a class="btn">Go!</a>
      </div>
    </div>
    <div class="universe__right__content__left__container__table__content">
      <table>
        <thead>
          <tr>
            <th>
            </th>
            <th>
              Planet
            </th>
            <th>
              Name
            </th>
            <th>
            Player
            </th>
            <th>
            Action
            </th>
          </tr>
        </thead>
        <tbody>
         
        </tbody>
      </table>
    </div>
    
  </div>
  <div class="universe__right__content__left__container__third">
    <div class="universe__right__content__left__container__third__section resources">
      <div class="universe__right__content__left__container__third__section__title">
        Buildings
      </div>
      <img src="/images/icon_b.svg" alt="bar" />
      <div class="universe__right__content__left__container__third__section__content">
        <div class="universe__right__content__left__container__third__section__content__corners">
          <img class="corner-left-top" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-right-top" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-right-bottom" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-left-bottom" src="/images/icon_c.svg" alt="corner" />
          <div class="universe__right__content__left__container__third__section__content__corners__buildings">
          </div>
        </div>
      </div>
    </div>
    <!--<div class="universe__right__content__left__container__third__section">
      <div class="universe__right__content__left__container__third__section__title">
        Hangar <img src="/images/icon_4.svg" alt="status" />
      </div>
      <img src="/images/icon_b.svg" alt="bar" />
      <div class="universe__right__content__left__container__third__section__content">
        <div class="universe__right__content__left__container__third__section__content__corners">
          <img class="corner-left-top" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-right-top" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-right-bottom" src="/images/icon_c.svg" alt="corner" />
          <img class="corner-left-bottom" src="/images/icon_c.svg" alt="corner" />
          <div class="universe__right__content__left__container__third__section__content__corners__text">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Ut molestie, elit non efficitur efficitur, libero arcu fermentum nunc, ac suscipit diam ex at ex.
          </div>
        </div>
      </div>
    </div>-->
  </div>
</div>
@endsection

@section('scripts')
<script>
  $(function() {
    $(`.universe__left__menu > ul > li > a`).removeClass('active');
    $(`.universe__left__menu > ul > li > a[href='/galaxy']`).addClass('active');
    tippy(`.tippy`, {
      placement: 'top',
      arrow: true,
      theme: 'translucent',
    });
  })
</script>
@endsection