<?php

return [
    'name'                        => 'Viettel Post',
    'settings'                    => 'Cài đặt Viettel Post',
    'shipping_method_name'        => 'Viettel Post - Giao hàng nhanh',
    'shipping_method_description' => 'Tích hợp vận chuyển Viettel Post cho Botble E-commerce',

    // Settings form
    'enable'                      => 'Kích hoạt Viettel Post',
    'username'                    => 'Tên đăng nhập / Email',
    'password'                    => 'Mật khẩu',
    'password_placeholder'        => 'Mật khẩu đăng nhập',
    'partner_code'                => 'Mã đối tác',
    'shop_id'                     => 'Mã cửa hàng',
    'customer_id'                 => 'Mã khách hàng',
    'default_service'             => 'Dịch vụ mặc định',
    'auto_create_shipment'        => 'Tự động tạo đơn vận chuyển',

    // Important notice
    'important_notice'            => 'Lưu ý quan trọng',
    'notice_api_address'          => 'Địa chỉ gửi hàng được lấy trực tiếp từ API Viettel Post',
    'notice_api_register'         => 'Đảm bảo tài khoản Viettel Post đã được đăng ký và kích hoạt API',
    'notice_credentials'          => 'Cần nhập đúng thông tin đăng nhập để lấy danh sách địa chỉ',

    // Configuration guide
    'configuration_guide'         => 'Hướng dẫn cấu hình Viettel Post',
    'config_steps'                => 'Các bước cấu hình:',
    'step_register'               => 'Đăng ký tài khoản đối tác Viettel Post',
    'step_enter_credentials'      => 'Nhập thông tin đăng nhập và lưu lại',
    'step_load_address'           => 'Click "Tải danh sách địa chỉ" để lấy tỉnh/quận từ API',
    'provide_credentials'         => 'Vui lòng cung cấp thông tin đăng nhập:',

    // Sender address
    'sender_address'              => 'Địa chỉ gửi hàng (Kho hàng)',
    'sender_address_note'         => 'Cần thiết để tính phí ship chính xác',
    'use_store_address'           => 'Sử dụng địa chỉ từ cửa hàng (Marketplace)',
    'use_store_address_on'        => 'Khi bật: Mỗi sản phẩm sẽ dùng địa chỉ của cửa hàng bán để tính phí ship.',
    'use_store_address_off'       => 'Khi tắt: Dùng địa chỉ cố định bên dưới cho tất cả.',
    'sender_province'             => 'Tỉnh/Thành phố',
    'sender_district'             => 'Quận/Huyện',
    'sender_ward'                 => 'Phường/Xã',
    'select_province'             => '-- Chọn Tỉnh/Thành --',
    'select_district'             => '-- Chọn Quận/Huyện --',
    'select_ward'                 => '-- Chọn Phường/Xã --',
    'select_province_first'       => 'Chọn Tỉnh trước...',
    'select_district_first'       => 'Chọn Quận trước...',
    'loading'                     => 'Đang tải...',
    'load_from_api'               => 'Tải từ API',
    'load_error'                  => 'Lỗi tải dữ liệu',

    // Inventory management
    'inventory_management'        => 'ViettelPost - Quản Lý Kho Hàng',
    'inventory_name'              => 'Tên kho hàng',
    'inventory_phone'             => 'Số điện thoại',
    'inventory_address'           => 'Địa chỉ',
    'register_inventory'          => 'Đăng ký kho hàng',
    'register_new_inventory'      => 'Đăng ký kho hàng mới',
    'link_existing_inventory'     => 'Liên kết kho có sẵn',
    'change_inventory'            => 'Thay đổi kho hàng',
    'current_inventory'           => 'Kho hàng hiện tại',
    'inventory_linked'            => 'Đã liên kết kho hàng:',
    'no_inventory'                => 'Chưa đăng ký kho hàng ViettelPost.',
    'no_inventory_note'           => 'Vui lòng đăng ký kho hàng để có thể tạo đơn vận chuyển ViettelPost.',
    'save_store_first'            => 'Vui lòng lưu thông tin cửa hàng trước, sau đó có thể đăng ký kho hàng ViettelPost.',
    'register_inventory_modal'    => 'Đăng ký kho hàng ViettelPost',

    // Messages
    'register_success'            => 'Đăng ký kho hàng thành công!',
    'link_success'                => 'Liên kết kho hàng thành công!',
    'error_connection'            => 'Không thể kết nối đến Viettel Post',
    'error_authentication'        => 'Thông tin đăng nhập không hợp lệ',
    'error_register'              => 'Không thể đăng ký kho hàng',

    // Buttons
    'register'                    => 'Đăng ký',
    'cancel'                      => 'Hủy',
    'save'                        => 'Lưu',
    'update'                      => 'Cập nhật',

    // Address labels
    'province'                    => 'Tỉnh/Thành phố',
    'district'                    => 'Quận/Huyện',
    'ward'                        => 'Phường/Xã',
];
