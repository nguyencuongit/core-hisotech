<header>
    {{-- Header Top --}}
    {!! Theme::partial('header.top', ['colorMode' => 'light', 'headerTopClass' => 'container-fluid pl-60 pr-60', 'showUserMenu' => true]) !!}

    {{-- Inline CSS for Header 6 --}}
    <style>
        .tp-header-6-wrapper {
            padding: 12px 0;
        }
        .tp-header-6-center {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        .tp-header-6-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: 15px;
        }
        .tp-header-6-action-item {
            position: relative;
        }
        .tp-header-6-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #f5f5f5;
            color: #010f1c;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .tp-header-6-action-btn:hover {
            background-color: var(--tp-theme-primary, #0989ff);
            color: #fff;
        }
        .tp-header-6-action-btn svg {
            width: 18px;
            height: 18px;
        }
        .tp-header-6-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            font-size: 10px;
            font-weight: 600;
            color: #fff;
            background-color: var(--tp-theme-primary, #0989ff);
            border-radius: 50%;
            line-height: 1;
        }
        .tp-header-6-contact {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .tp-header-6-zalo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #010f1c;
            transition: all 0.3s ease;
        }
        .tp-header-6-zalo:hover {
            color: #0068ff;
        }
        .tp-header-6-zalo-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #0068ff;
            color: #fff;
        }
        .tp-header-6-zalo-icon svg {
            width: 22px;
            height: 22px;
        }
        .tp-header-6-phone {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #010f1c;
            transition: all 0.3s ease;
        }
        .tp-header-6-phone:hover {
            color: var(--tp-theme-primary, #0989ff);
        }
        .tp-header-6-phone-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #f5f5f5;
            color: var(--tp-theme-primary, #0989ff);
        }
        .tp-header-6-phone-icon svg {
            width: 18px;
            height: 18px;
        }
        .tp-header-6-phone-text {
            line-height: 1.3;
        }
        .tp-header-6-phone-text span {
            display: block;
            font-size: 11px;
            color: #777;
        }
        .tp-header-6-phone-text p {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--tp-theme-primary, #0989ff);
        }
        @media (max-width: 1199px) {
            .tp-header-6-phone-text {
                display: none;
            }
        }
        @media (max-width: 991px) {
            .tp-header-6-contact {
                display: none !important;
            }
        }
        @media (max-width: 575px) {
            .tp-header-6-action-btn {
                width: 34px;
                height: 34px;
            }
            .tp-header-6-action-btn svg {
                width: 16px;
                height: 16px;
            }
            .tp-header-6-actions {
                gap: 4px;
                margin-left: 10px;
            }
        }

        /* ========================================
           Enhanced Submenu Styles - Cấp 2 & 3
        ======================================== */
        .main-menu > nav > ul > li > .tp-submenu {
            border-radius: 12px;
            padding: 16px 0;
            min-width: 240px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 
                        0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
            transform: translateY(15px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible;
        }

        /* Mũi tên chỉ lên menu cấp 2 */
        .main-menu > nav > ul > li > .tp-submenu::before {
            content: '';
            position: absolute;
            top: -8px;
            left: 24px;
            width: 16px;
            height: 16px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-bottom: none;
            border-right: none;
            transform: rotate(45deg);
            border-radius: 3px 0 0 0;
        }

        /* Menu items cấp 2 */
        .main-menu > nav > ul > li > .tp-submenu > li {
            padding: 0 16px;
            margin: 0;
            list-style: none;
        }

        .main-menu > nav > ul > li > .tp-submenu > li > a {
            display: flex;
            align-items: center;
            padding: 12px 16px !important;
            font-size: 14px;
            font-weight: 500;
            color: var(--tp-text-body, #5b6c8f);
            border-radius: 8px;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .main-menu > nav > ul > li > .tp-submenu > li > a:hover {
            color: var(--tp-theme-primary, #0989ff);
            background: rgba(9, 137, 255, 0.06);
            transform: translateX(5px);
        }

        /* Separator giữa các items */
        .main-menu > nav > ul > li > .tp-submenu > li:not(:last-child)::after {
            content: '';
            display: block;
            height: 1px;
            margin: 2px 16px 0;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent);
        }

        /* Hiển thị menu cấp 2 khi hover */
        .main-menu > nav > ul > li:hover > .tp-submenu {
            transform: translateY(0);
        }

        /* ========== Menu cấp 3 ========== */
        .main-menu > nav > ul > li > .tp-submenu li > .tp-submenu {
            border-radius: 12px;
            padding: 16px 0;
            min-width: 220px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12),
                        0 4px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.98);
            left: calc(100% + 8px) !important;
            top: -10px !important;
            transform: translateX(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible;
        }

        /* Mũi tên chỉ trái menu cấp 3 */
        .main-menu > nav > ul > li > .tp-submenu li > .tp-submenu::before {
            content: '';
            position: absolute;
            top: 24px;
            left: -8px;
            width: 14px;
            height: 14px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-top: none;
            border-right: none;
            transform: rotate(45deg);
            border-radius: 0 0 0 3px;
        }

        .main-menu > nav > ul > li > .tp-submenu li > .tp-submenu li {
            padding: 0 12px;
        }

        .main-menu > nav > ul > li > .tp-submenu li > .tp-submenu li a {
            padding: 10px 14px !important;
            font-size: 13px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .main-menu > nav > ul > li > .tp-submenu li > .tp-submenu li a:hover {
            color: var(--tp-theme-primary, #0989ff);
            background: rgba(9, 137, 255, 0.06);
            transform: translateX(5px);
        }

        /* Hiển thị menu cấp 3 khi hover */
        .main-menu > nav > ul > li > .tp-submenu li:hover > .tp-submenu {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        /* Animation fade in cho menu items */
        @keyframes submenuFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .main-menu > nav > ul > li > .tp-submenu > li {
            opacity: 0;
            animation: submenuFadeIn 0.3s ease forwards;
        }

        .main-menu > nav > ul > li:hover > .tp-submenu > li {
            opacity: 1;
        }

        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(1) { animation-delay: 0.04s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(2) { animation-delay: 0.08s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(3) { animation-delay: 0.12s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(4) { animation-delay: 0.16s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(5) { animation-delay: 0.20s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(6) { animation-delay: 0.24s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(7) { animation-delay: 0.28s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(8) { animation-delay: 0.32s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(9) { animation-delay: 0.36s; }
        .main-menu > nav > ul > li > .tp-submenu > li:nth-child(10) { animation-delay: 0.40s; }

    </style>

    {{-- Header Main --}}
    <div
        id="header-sticky"
        @class([
            'tp-header-area tp-header-sticky tp-header-height',
            'header-main' => ! Theme::get('hasSlider'),
            'tp-header-style-transparent-white tp-header-transparent' => Theme::get('hasSlider')
        ])
        {!! Theme::partial('header.sticky-data') !!}
    >
        <div class="tp-header-6-wrapper pl-60 pr-60" style="background-color: {{ $headerMainBackgroundColor }}; color: {{ $headerMainTextColor }}">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-start">
                    {{-- Logo --}}
                    <div class="col-xl-3 col-lg-3 col-4" style="display: flex; justify-content: flex-end;">
                        {!! Theme::partial('header.logo') !!}
                    </div>

                    {{-- Main Menu + Actions --}}
                    <div class="col-xl-7 col-lg-7 d-none d-lg-block">
                        <div class="tp-header-6-center">
                            <div class="main-menu menu-style-3 p-relative">
                                <nav class="tp-main-menu-content">
                                    {!! Menu::renderMenuLocation('main-menu', ['view' => 'main-menu']) !!}
                                </nav>
                            </div>
                            
                            {{-- Actions next to menu --}}
                            
                        </div>
                        
                        @if(is_plugin_active('ecommerce'))
                            <div class="tp-category-menu-wrapper d-none">
                                <nav class="tp-category-menu-content">
                                    {!! Theme::partial('header.categories-dropdown') !!}
                                </nav>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-2 col-2 d-none d-lg-block">
                        @if(is_plugin_active('ecommerce'))
                                <div class="tp-header-6-actions">
                                    {{-- Search --}}
                                    <div class="tp-header-6-action-item">
                                        <button type="button" class="tp-header-6-action-btn tp-search-open-btn" aria-label="{{ __('Open search') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                                <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Wishlist --}}
                                    @if (EcommerceHelper::isWishlistEnabled())
                                        <div class="tp-header-6-action-item">
                                            <a href="{{ route('public.wishlist') }}" class="tp-header-6-action-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                                    <path d="M178,32c-20.65,0-38.73,8.88-50,23.89C116.73,40.88,98.65,32,78,32A62.07,62.07,0,0,0,16,94c0,70,103.79,126.66,108.21,129a8,8,0,0,0,7.58,0C136.21,220.66,240,164,240,94A62.07,62.07,0,0,0,178,32ZM128,206.8C109.74,196.16,32,147.69,32,94A46.06,46.06,0,0,1,78,48c19.45,0,35.78,10.36,42.6,27a8,8,0,0,0,14.8,0c6.82-16.67,23.15-27,42.6-27a46.06,46.06,0,0,1,46,46C224,147.61,146.24,196.15,128,206.8Z"></path>
                                                </svg>
                                                <span class="tp-header-6-badge" data-bb-value="wishlist-count">{{ Cart::instance('wishlist')->count() }}</span>
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Cart --}}
                                    @if (EcommerceHelper::isCartEnabled())
                                        <div class="tp-header-6-action-item">
                                            <button type="button" class="tp-header-6-action-btn cartmini-open-btn" data-bb-toggle="open-mini-cart" data-url="{{ route('public.ajax.cart-content') }}" aria-label="{{ __('View cart') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                                    <path d="M222.14,58.87A8,8,0,0,0,216,56H54.68L49.79,29.14A16,16,0,0,0,34.05,16H16a8,8,0,0,0,0,16h18L65.21,172.86A24,24,0,0,0,88.83,192H184a24,24,0,0,0,23.62-19.66l18.66-93.31A8,8,0,0,0,222.14,58.87ZM191.66,163.22A8,8,0,0,1,184,176H88.83a8,8,0,0,1-7.87-6.57L57.88,72H206.12ZM96,216a16,16,0,1,1-16-16A16,16,0,0,1,96,216Zm112,0a16,16,0,1,1-16-16A16,16,0,0,1,192,216Z"></path>
                                                </svg>
                                                <span class="tp-header-6-badge" data-bb-value="cart-count">{{ Cart::instance('cart')->count() }}</span>
                                            </button>
                                        </div>
                                    @endif
                                    
                                    {{-- Zalo --}}
                      
                                        <div class="tp-header-6-action-item">
                                            <a href="https://zalo.me/{{ theme_option('hotline', '0909090909') }}" class="tp-header-6-action-btn" target="_blank" aria-label="Zalo" style="background-color: #0068ff">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 614.501 613.667" fill="#FFFFFF" style="width: 22px; height: 22px;">
                                                    <path d="M464.721,301.399c-13.984-0.014-23.707,11.478-23.944,28.312c-0.251,17.771,9.168,29.208,24.037,29.202c14.287-0.007,23.799-11.095,24.01-27.995C489.028,313.536,479.127,301.399,464.721,301.399z"/>
                                                    <path d="M291.83,301.392c-14.473-0.316-24.578,11.603-24.604,29.024c-0.02,16.959,9.294,28.259,23.496,28.502c15.072,0.251,24.592-10.87,24.539-28.707C315.214,313.318,305.769,301.696,291.83,301.392z"/>
                                                    <path d="M310.518,3.158C143.102,3.158,7.375,138.884,7.375,306.3s135.727,303.142,303.143,303.142c167.415,0,303.143-135.727,303.143-303.142S477.933,3.158,310.518,3.158z M217.858,391.083c-33.364,0.818-66.828,1.353-100.133-0.343c-21.326-1.095-27.652-18.647-14.248-36.583c21.55-28.826,43.886-57.065,65.792-85.621c2.546-3.305,6.214-5.996,7.15-12.705c-16.609,0-32.784,0.04-48.958-0.013c-19.195-0.066-28.278-5.805-28.14-17.652c0.132-11.768,9.175-17.329,28.397-17.348c25.159-0.026,50.324-0.06,75.476,0.026c9.637,0.033,19.604,0.105,25.304,9.789c6.22,10.561,0.284,19.512-5.646,27.454c-21.26,28.497-43.015,56.624-64.559,84.902c-2.599,3.41-5.119,6.88-9.453,12.725c23.424,0,44.123-0.053,64.816,0.026c8.674,0.026,16.662,1.873,19.941,11.267C237.892,379.329,231.368,390.752,217.858,391.083z M350.854,330.211c0,13.417-0.093,26.841,0.039,40.265c0.073,7.599-2.599,13.647-9.512,17.084c-7.296,3.642-14.71,3.028-20.304-2.968c-3.997-4.281-6.214-3.213-10.488-0.422c-17.955,11.728-39.908,9.96-56.597-3.866c-29.928-24.789-30.026-74.803-0.211-99.776c16.194-13.562,39.592-15.462,56.709-4.143c3.951,2.619,6.201,4.815,10.396-0.053c5.39-6.267,13.055-6.761,20.271-3.357c7.454,3.509,9.935,10.165,9.776,18.265C350.67,304.222,350.86,317.217,350.854,330.211z M395.617,369.579c-0.118,12.837-6.398,19.783-17.196,19.908c-10.779,0.132-17.593-6.966-17.646-19.512c-0.179-43.352-0.185-86.696,0.007-130.041c0.059-12.256,7.302-19.921,17.896-19.222c11.425,0.752,16.992,7.448,16.992,18.833c0,22.104,0,44.216,0,66.327C395.677,327.105,395.828,348.345,395.617,369.579z M463.981,391.868c-34.399-0.336-59.037-26.444-58.786-62.289c0.251-35.66,25.304-60.713,60.383-60.396c34.631,0.304,59.374,26.306,58.998,61.986C524.207,366.492,498.534,392.205,463.981,391.868z"/>
                                                </svg>
                                            </a>
                                        </div>
 
                                </div>

                                
                            @endif
                    </div>
                    {{-- Mobile Actions Only --}}
                    <div class="col-8 d-lg-none">
                        <div class="d-flex align-items-center justify-content-end">

                            {{-- Mobile Actions --}}
                            <div class="d-flex align-items-center gap-2">
                                @if(is_plugin_active('ecommerce'))
                                    {{-- Search Mobile --}}
                                    <div class="tp-header-6-action-item d-none d-sm-block">
                                        <button type="button" class="tp-header-6-action-btn tp-search-open-btn" aria-label="{{ __('Open search') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                                <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Cart Mobile --}}
                                    @if (EcommerceHelper::isCartEnabled())
                                        <div class="tp-header-6-action-item">
                                            <button type="button" class="tp-header-6-action-btn cartmini-open-btn" data-bb-toggle="open-mini-cart" data-url="{{ route('public.ajax.cart-content') }}" aria-label="{{ __('View cart') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                                    <path d="M222.14,58.87A8,8,0,0,0,216,56H54.68L49.79,29.14A16,16,0,0,0,34.05,16H16a8,8,0,0,0,0,16h18L65.21,172.86A24,24,0,0,0,88.83,192H184a24,24,0,0,0,23.62-19.66l18.66-93.31A8,8,0,0,0,222.14,58.87ZM191.66,163.22A8,8,0,0,1,184,176H88.83a8,8,0,0,1-7.87-6.57L57.88,72H206.12ZM96,216a16,16,0,1,1-16-16A16,16,0,0,1,96,216Zm112,0a16,16,0,1,1-16-16A16,16,0,0,1,192,216Z"></path>
                                                </svg>
                                                <span class="tp-header-6-badge" data-bb-value="cart-count">{{ Cart::instance('cart')->count() }}</span>
                                            </button>
                                        </div>
                                    @endif
                                @endif

                                {{-- Mobile Menu Toggle --}}
                                <div class="tp-header-6-action-item">
                                    <button type="button" class="tp-header-6-action-btn tp-offcanvas-open-btn" aria-label="{{ __('Open menu') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor">
                                            <path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128ZM40,72H216a8,8,0,0,0,0-16H40a8,8,0,0,0,0,16ZM216,184H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
