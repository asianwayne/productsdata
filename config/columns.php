<?php
// Column definitions �C single source of truth for CSV import, DB fields, forms, filters and export.
//
// Keys:
//   field      �C database column name
//   label      �C Chinese label (must match CSV header exactly)
//   type       �C 'text' | 'number'  (controls form input type)
//   filterable �C appear in the search/filter panel
//   list       �C appear as a column in the products index table
//
// To add a new column: add an entry here + add the column to setup.sql + run ALTER TABLE.

return [
    // ���� ������Ϣ ����������������������������������������������������������������������������������������������������������������������������
    ['field' => 'name',              'label' => '����',            'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '������Ϣ'],
    ['field' => 'tqb_code',          'label' => 'TQB����',         'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '������Ϣ'],
    ['field' => 'oem_number',        'label' => 'OEM����',         'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '������Ϣ'],
    ['field' => 'production_code',   'label' => '������',          'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '������Ϣ'],
    ['field' => 'no_stock_purchase', 'label' => '�޿����ɹ�',    'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '������Ϣ'],

    // ���� �������� ����������������������������������������������������������������������������������������������������������������������������
    ['field' => 'car_series',        'label' => '��ϵ',            'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '��������'],
    ['field' => 'car_model',         'label' => '����',            'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '��������'],
    ['field' => 'universal_model',   'label' => 'ͨ�ó���',        'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��������'],
    ['field' => 'trade_car_series',  'label' => '��ó��ϵ',        'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��������'],
    ['field' => 'trade_car_model',   'label' => '��ó����',        'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��������'],
    ['field' => 'trade_universal',   'label' => '��óͨ�ó���',    'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '��������'],

    // ���� ��Ʒ���� ����������������������������������������������������������������������������������������������������������������������������
    ['field' => 'bca',               'label' => 'BCA',             'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'skf',               'label' => 'SKF',             'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'snr',               'label' => 'SNR',             'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'timken',            'label' => 'TIMKEN',          'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'nsk',               'label' => 'NSK',             'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'ntn',               'label' => 'NTN',             'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],
    ['field' => 'koyo',              'label' => 'KOYO',            'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ʒ����'],

    // ���� ������ ����������������������������������������������������������������������������������������������������������������������������
    ['field' => 'dimensions',        'label' => '�ߴ�',            'type' => 'text',   'filterable' => false, 'list' => true,  'tab' => '������'],
    ['field' => 'weight',            'label' => '����',            'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '������'],
    ['field' => 'inner_box_size',    'label' => '�ںгߴ�',        'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '������'],
    ['field' => 'spline_teeth',      'label' => '����/�ż�/��Ȧ����', 'type' => 'text', 'filterable' => false, 'list' => false, 'tab' => '������'],
    ['field' => 'cost',              'label' => '�ɱ�',            'type' => 'number', 'filterable' => false, 'list' => true,  'tab' => '������'],

    // ���� ������״̬ ������������������������������������������������������������������������������������������������������������������������
    ['field' => 'original_category', 'label' => 'ԭ������',        'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '����״̬'],
    ['field' => 'stock_status',      'label' => '���״̬',        'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '����״̬'],
    ['field' => 'in_system',         'label' => '�Ƿ���¼��ϵͳ',  'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '����״̬'],
    ['field' => 'system_code',       'label' => 'ϵͳ��������',    'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '����״̬'],
    ['field' => 'warehouse_a',       'label' => 'A�ֿɳ��ж�',     'type' => 'text',   'filterable' => true,  'list' => true,  'tab' => '����״̬'],

    // ���� ��� ������������������������������������������������������������������������������������������������������������������������������������
    ['field' => 'stock_qty',         'label' => '�������',        'type' => 'number', 'filterable' => false, 'list' => true,  'tab' => '���'],
    ['field' => 'stock_max',         'label' => '�������',        'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '���'],
    ['field' => 'stock_min',         'label' => '�������',        'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '���'],

    // ���� ��Ӧ�� ��������������������������������������������������������������������������������������������������������������������������������
    ['field' => 'supplier1',         'label' => '��ѡ��Ӧ��',      'type' => 'text',   'filterable' => true,  'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier1_price',   'label' => '��ѡ�ɹ���',      'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier2',         'label' => '���ù�Ӧ��1',     'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier2_price',   'label' => '���òɹ���1',     'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier3',         'label' => '���ù�Ӧ��2',     'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier3_price',   'label' => '���òɹ���2',     'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier4',         'label' => '���ù�Ӧ��3',     'type' => 'text',   'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
    ['field' => 'supplier4_price',   'label' => '���òɹ���3',     'type' => 'number', 'filterable' => false, 'list' => false, 'tab' => '��Ӧ��'],
];
