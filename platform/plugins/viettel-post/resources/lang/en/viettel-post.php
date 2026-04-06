<?php

return [
    'name'                        => 'Viettel Post',
    'settings'                    => 'Viettel Post Settings',
    'shipping_method_name'        => 'Viettel Post - Fast Delivery',
    'shipping_method_description' => 'Viettel Post shipping integration for Botble E-commerce',

    // Settings form
    'enable'                      => 'Enable Viettel Post',
    'username'                    => 'Username / Email',
    'password'                    => 'Password',
    'password_placeholder'        => 'Login password',
    'partner_code'                => 'Partner Code',
    'shop_id'                     => 'Shop ID',
    'customer_id'                 => 'Customer ID',
    'default_service'             => 'Default Service',
    'auto_create_shipment'        => 'Auto Create Shipment',

    // Important notice
    'important_notice'            => 'Important Notice',
    'notice_api_address'          => 'Sender address is fetched directly from Viettel Post API',
    'notice_api_register'         => 'Ensure your Viettel Post account is registered and API activated',
    'notice_credentials'          => 'Enter correct login credentials to fetch address list',

    // Configuration guide
    'configuration_guide'         => 'Viettel Post Configuration Guide',
    'config_steps'                => 'Configuration steps:',
    'step_register'               => 'Register a Viettel Post partner account',
    'step_enter_credentials'      => 'Enter login information and save',
    'step_load_address'           => 'Click "Load address list" to fetch provinces/districts from API',
    'provide_credentials'         => 'Please provide login information:',

    // Sender address
    'sender_address'              => 'Sender Address (Warehouse)',
    'sender_address_note'         => 'Required for accurate shipping fee calculation',
    'use_store_address'           => 'Use Store Address (Marketplace)',
    'use_store_address_on'        => 'When ON: Each product uses its store address for shipping calculation.',
    'use_store_address_off'       => 'When OFF: Use the fixed address below for all.',
    'sender_province'             => 'Province/City',
    'sender_district'             => 'District',
    'sender_ward'                 => 'Ward',
    'select_province'             => '-- Select Province --',
    'select_district'             => '-- Select District --',
    'select_ward'                 => '-- Select Ward --',
    'select_province_first'       => 'Select Province first...',
    'select_district_first'       => 'Select District first...',
    'loading'                     => 'Loading...',
    'load_from_api'               => 'Load from API',
    'load_error'                  => 'Error loading data',

    // Inventory management
    'inventory_management'        => 'ViettelPost - Inventory Management',
    'inventory_name'              => 'Warehouse Name',
    'inventory_phone'             => 'Phone Number',
    'inventory_address'           => 'Address',
    'register_inventory'          => 'Register Inventory',
    'register_new_inventory'      => 'Register New Inventory',
    'link_existing_inventory'     => 'Link Existing Inventory',
    'change_inventory'            => 'Change Inventory',
    'current_inventory'           => 'Current Inventory',
    'inventory_linked'            => 'Inventory Linked:',
    'no_inventory'                => 'No ViettelPost inventory registered.',
    'no_inventory_note'           => 'Please register inventory to create ViettelPost shipments.',
    'save_store_first'            => 'Please save the store information first, then you can register ViettelPost inventory.',
    'register_inventory_modal'    => 'Register ViettelPost Inventory',

    // Messages
    'register_success'            => 'Inventory registered successfully!',
    'link_success'                => 'Inventory linked successfully!',
    'error_connection'            => 'Cannot connect to Viettel Post',
    'error_authentication'        => 'Invalid credentials',
    'error_register'              => 'Cannot register inventory',

    // Buttons
    'register'                    => 'Register',
    'cancel'                      => 'Cancel',
    'save'                        => 'Save',
    'update'                      => 'Update',

    // Address labels
    'province'                    => 'Province/City',
    'district'                    => 'District',
    'ward'                        => 'Ward',
];
