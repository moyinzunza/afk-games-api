@extends('layouts.game')

@section('styles')

@endsection

@section('content')
<div class="universe__right__content__left__container resources">
  <div class="universe__right__content__left__container__first">
    <img src="/images/cuadro.png" alt="bg" />
    <div class="universe__right__content__left__container__first__content">
      <div class="universe__right__content__left__container__first__content__img">
      </div>
      <div class="universe__right__content__left__container__first__content__text">
      </div>
    </div>
    <div class="universe__right__content__left__container__first__popup">

    </div>
  </div>
  <div class="universe__right__content__left__container__second resources">
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
    $(`.universe__left__menu > ul > li > a[href='/technologies']`).addClass('active');
  })
</script>
@endsection