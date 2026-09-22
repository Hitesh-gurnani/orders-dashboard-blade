<?php

declare(strict_types=1);

/**
 * 44 orders for Northwind Supply Co., a wholesale distributor.
 *
 * Wholesale is the reason the totals span $44.00 to $16,900.00 instead of
 * clustering: a table whose numbers are all four digits never shows whether the
 * alignment is doing anything.
 *
 * Totals are SIGNED INTEGER CENTS. Refunded orders are stored negative because
 * the money genuinely moved the other way -- the minus in the Total column is
 * data, not a presentation rule applied to a positive number.
 *
 * Deliberately present: one customer name long enough to force truncation,
 * diacritics in several names so the encoding and font fallback are exercised
 * rather than assumed, and at least one of every status inside the first page.
 */

return [
    ['id' => 4821, 'number' => '#10-4821', 'customer' => 'Ana Mota',                          'placed' => '2026-09-22T09:41:00', 'items' => 3,  'total_cents' => 412000,   'status' => 'fulfilled'],
    ['id' => 4820, 'number' => '#10-4820', 'customer' => 'Terrance Whitfield',                'placed' => '2026-09-22T09:12:00', 'items' => 1,  'total_cents' => 38950,    'status' => 'processing'],
    ['id' => 4819, 'number' => '#10-4819', 'customer' => 'Kepler Dynamics AB',                'placed' => '2026-09-22T08:57:00', 'items' => 12, 'total_cents' => 1314075,  'status' => 'fulfilled'],
    ['id' => 4818, 'number' => '#10-4818', 'customer' => 'Sofia Lindqvist',                   'placed' => '2026-09-21T22:04:00', 'items' => 2,  'total_cents' => -147220,  'status' => 'refunded'],
    ['id' => 4817, 'number' => '#10-4817', 'customer' => 'Marcus Bell',                       'placed' => '2026-09-21T21:36:00', 'items' => 1,  'total_cents' => 6200,     'status' => 'cancelled'],
    ['id' => 4816, 'number' => '#10-4816', 'customer' => 'Halcyon Freight Co',                'placed' => '2026-09-21T19:02:00', 'items' => 7,  'total_cents' => 904000,   'status' => 'fulfilled'],
    ['id' => 4815, 'number' => '#10-4815', 'customer' => 'Priya Raghunathan',                 'placed' => '2026-09-21T18:44:00', 'items' => 2,  'total_cents' => 73899,    'status' => 'fulfilled'],
    ['id' => 4814, 'number' => '#10-4814', 'customer' => 'Ines Okonkwo',                      'placed' => '2026-09-21T16:20:00', 'items' => 4,  'total_cents' => 261540,   'status' => 'processing'],
    ['id' => 4813, 'number' => '#10-4813', 'customer' => 'Bao Nguyen',                        'placed' => '2026-09-21T14:08:00', 'items' => 1,  'total_cents' => 48675,    'status' => 'fulfilled'],
    ['id' => 4812, 'number' => '#10-4812', 'customer' => 'Meridian Fabrication Partners LLC', 'placed' => '2026-09-21T11:55:00', 'items' => 18, 'total_cents' => 590000,   'status' => 'fulfilled'],
    ['id' => 4811, 'number' => '#10-4811', 'customer' => 'Tomás Ferreira',                    'placed' => '2026-09-20T20:31:00', 'items' => 2,  'total_cents' => 120450,   'status' => 'processing'],
    ['id' => 4810, 'number' => '#10-4810', 'customer' => 'Aoife Brennan',                     'placed' => '2026-09-20T17:49:00', 'items' => 1,  'total_cents' => 31800,    'status' => 'cancelled'],
    ['id' => 4809, 'number' => '#10-4809', 'customer' => 'Delacroix Frères SARL',             'placed' => '2026-09-20T15:02:00', 'items' => 9,  'total_cents' => 746280,   'status' => 'fulfilled'],
    ['id' => 4808, 'number' => '#10-4808', 'customer' => 'Grete Halvorsen',                   'placed' => '2026-09-20T13:27:00', 'items' => 3,  'total_cents' => 94425,    'status' => 'fulfilled'],
    ['id' => 4807, 'number' => '#10-4807', 'customer' => 'Yusuf Demir',                       'placed' => '2026-09-20T10:11:00', 'items' => 1,  'total_cents' => -23310,   'status' => 'refunded'],
    ['id' => 4806, 'number' => '#10-4806', 'customer' => 'Northgate Marine Services',         'placed' => '2026-09-19T21:40:00', 'items' => 6,  'total_cents' => 387500,   'status' => 'fulfilled'],
    ['id' => 4805, 'number' => '#10-4805', 'customer' => 'Wen Li',                            'placed' => '2026-09-19T18:06:00', 'items' => 2,  'total_cents' => 165000,   'status' => 'processing'],
    ['id' => 4804, 'number' => '#10-4804', 'customer' => 'Rosalind Achebe',                   'placed' => '2026-09-19T16:33:00', 'items' => 1,  'total_cents' => 52995,    'status' => 'fulfilled'],
    ['id' => 4803, 'number' => '#10-4803', 'customer' => 'Stellar Compound Works',            'placed' => '2026-09-19T12:18:00', 'items' => 14, 'total_cents' => 1120840,  'status' => 'fulfilled'],
    ['id' => 4802, 'number' => '#10-4802', 'customer' => 'Hana Kowalczyk',                    'placed' => '2026-09-19T09:04:00', 'items' => 5,  'total_cents' => 204630,   'status' => 'fulfilled'],

    ['id' => 4801, 'number' => '#10-4801', 'customer' => 'Dmitri Sokolov',                    'placed' => '2026-09-18T22:15:00', 'items' => 3,  'total_cents' => 248000,   'status' => 'fulfilled'],
    ['id' => 4800, 'number' => '#10-4800', 'customer' => 'Fatima Al-Rashid',                  'placed' => '2026-09-18T19:47:00', 'items' => 2,  'total_cents' => 87560,    'status' => 'fulfilled'],
    ['id' => 4799, 'number' => '#10-4799', 'customer' => 'Vantage Composites plc',            'placed' => '2026-09-18T15:29:00', 'items' => 22, 'total_cents' => 1690000,  'status' => 'fulfilled'],
    ['id' => 4798, 'number' => '#10-4798', 'customer' => 'Oliver Nakamura',                   'placed' => '2026-09-18T11:02:00', 'items' => 1,  'total_cents' => -41275,   'status' => 'refunded'],
    ['id' => 4797, 'number' => '#10-4797', 'customer' => 'Chidi Eze',                         'placed' => '2026-09-17T20:55:00', 'items' => 2,  'total_cents' => 103820,   'status' => 'fulfilled'],
    ['id' => 4796, 'number' => '#10-4796', 'customer' => 'Brightline Tooling',                'placed' => '2026-09-17T16:41:00', 'items' => 8,  'total_cents' => 633000,   'status' => 'fulfilled'],
    ['id' => 4795, 'number' => '#10-4795', 'customer' => 'Maya Rosenthal',                    'placed' => '2026-09-17T13:12:00', 'items' => 1,  'total_cents' => 14999,    'status' => 'processing'],
    ['id' => 4794, 'number' => '#10-4794', 'customer' => 'Anders Kjeldsen',                   'placed' => '2026-09-16T21:08:00', 'items' => 4,  'total_cents' => 320450,   'status' => 'fulfilled'],
    ['id' => 4793, 'number' => '#10-4793', 'customer' => 'Leila Haddad',                      'placed' => '2026-09-16T17:36:00', 'items' => 2,  'total_cents' => 98800,    'status' => 'fulfilled'],
    ['id' => 4792, 'number' => '#10-4792', 'customer' => 'Copperfield Industrial',            'placed' => '2026-09-16T14:20:00', 'items' => 7,  'total_cents' => 571235,   'status' => 'fulfilled'],
    ['id' => 4791, 'number' => '#10-4791', 'customer' => 'Jonas Bergström',                   'placed' => '2026-09-15T22:44:00', 'items' => 1,  'total_cents' => 27440,    'status' => 'processing'],
    ['id' => 4790, 'number' => '#10-4790', 'customer' => 'Amara Diallo',                      'placed' => '2026-09-15T18:53:00', 'items' => 3,  'total_cents' => 186000,   'status' => 'fulfilled'],
    ['id' => 4789, 'number' => '#10-4789', 'customer' => 'Orinoco Chemicals SA',              'placed' => '2026-09-15T12:07:00', 'items' => 11, 'total_cents' => 945500,   'status' => 'fulfilled'],
    ['id' => 4788, 'number' => '#10-4788', 'customer' => 'Ruth Vandermeer',                   'placed' => '2026-09-14T20:19:00', 'items' => 1,  'total_cents' => 62015,    'status' => 'cancelled'],
    ['id' => 4787, 'number' => '#10-4787', 'customer' => 'Kenji Matsumoto',                   'placed' => '2026-09-14T16:02:00', 'items' => 4,  'total_cents' => 219380,   'status' => 'fulfilled'],
    ['id' => 4786, 'number' => '#10-4786', 'customer' => 'Elin Karlsson',                     'placed' => '2026-09-13T19:31:00', 'items' => 1,  'total_cents' => 4400,     'status' => 'processing'],
    ['id' => 4785, 'number' => '#10-4785', 'customer' => 'Ardent Rail Group',                 'placed' => '2026-09-12T15:48:00', 'items' => 10, 'total_cents' => 788060,   'status' => 'fulfilled'],
    ['id' => 4784, 'number' => '#10-4784', 'customer' => 'Nadia Petrov',                      'placed' => '2026-09-11T17:22:00', 'items' => 2,  'total_cents' => 132500,   'status' => 'fulfilled'],
    ['id' => 4783, 'number' => '#10-4783', 'customer' => 'Samuel Adeyemi',                    'placed' => '2026-09-10T13:09:00', 'items' => 1,  'total_cents' => -36845,   'status' => 'refunded'],
    ['id' => 4782, 'number' => '#10-4782', 'customer' => 'Kestrel Packaging Ltd',             'placed' => '2026-09-08T18:37:00', 'items' => 6,  'total_cents' => 405000,   'status' => 'fulfilled'],
    ['id' => 4781, 'number' => '#10-4781', 'customer' => 'Beatriz Alvarez',                   'placed' => '2026-09-06T16:14:00', 'items' => 3,  'total_cents' => 274090,   'status' => 'fulfilled'],
    ['id' => 4780, 'number' => '#10-4780', 'customer' => 'Ravi Menon',                        'placed' => '2026-09-05T11:41:00', 'items' => 2,  'total_cents' => 110265,   'status' => 'processing'],
    ['id' => 4779, 'number' => '#10-4779', 'customer' => 'Thornbury Glassworks',              'placed' => '2026-09-03T14:56:00', 'items' => 16, 'total_cents' => 1360000,  'status' => 'fulfilled'],
    ['id' => 4778, 'number' => '#10-4778', 'customer' => 'Siobhán Gallagher',                 'placed' => '2026-09-01T10:23:00', 'items' => 2,  'total_cents' => 80620,    'status' => 'fulfilled'],
];
