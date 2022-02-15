<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1080, initial-scale=1">
    <title>Universe</title>
    <meta charset="utf-8" />
    <link href="/css/styles.scss" rel="stylesheet" type="text/css" />
    @yield('styles')
</head>

<body>
    <div class="universe">
        <div class="universe__left">
            <a href="/">
                <img src="/images/logo.svg" alt="universe" />
            </a>
            <div class="universe__left__menu">
                <ul>
                    <li>
                        <a href="/">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Resources
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Merchant
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Research
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Hangar
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Defending
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Fleet
                        </a>
                    </li>
                    <li>
                        <a href="/">
                            Galaxy
                        </a>
                    </li>
                </ul>
            </div>
            <div class="universe__left__btns">
                <a class="btn" href="/">
                    Casino
                </a>
                <a class="btn" href="/">
                    Store
                </a>
                <a class="btn" href="/">
                    Rewards
                </a>
            </div>

            <img src="/images/icon_b.svg" alt="bar" />
        </div>
        <div class="universe__right">
            <div class="universe__right__header">
                <div class="universe__right__header__language">
                    Language:
                    <div id="google_translate_element"></div>
                    <script type="text/javascript">
                        function googleTranslateElementInit() {
                            new google.translate.TranslateElement({
                                pageLanguage: 'en'
                            }, 'google_translate_element');
                        }
                    </script>
                </div>
                <div class="universe__right__header__icons">
                    <a href="/">
                        <img src="/images/icon_1.svg" alt="email" />
                    </a>
                    <a href="/">
                        <img src="/images/icon_2.svg" alt="Message" />
                    </a>
                    <a class="logout" href="/">
                        Logout
                    </a>
                </div>
                <div class="universe__right__header__menu">
                    <img src="/images/line.svg" alt="Line" />
                    <ul>
                        <li>
                            <a href="/">
                                Notes
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                Friends
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                Search
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                Settings
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                Support
                            </a>
                        </li>
                    </ul>
                    <img src="/images/line.svg" alt="Line" />
                </div>
            </div>
            <div class="universe__right__content">
                <div class="universe__right__content__left">
                    @yield('content')
                </div>
                <div class="universe__right__content__right">
                    <div class="universe__right__content__right__classification">
                        <p>
                            Resources
                        </p>
                        <div class="universe__right__content__right__classification__items">
                        </div>
                    </div>
                    <img src="/images/icon_b.svg" alt="bar" />
                    <div class="universe__right__content__right__planets">
                        <p>
                            Planets
                        </p>
                        <div class="universe__right__content__right__planets__items">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/js/jquery.js"></script>
    <script src="/js/scripts.js"></script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>

</html>