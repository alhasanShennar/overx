<?php

namespace App\Support;

class AdminPermission
{
    public const VIEW_POOL = 'view_pool';

    public const VIEW_PLATFORM_HOLDINGS = 'view_platform_holdings';

    public const VIEW_EARNINGS = 'view_earnings';

    public const VIEW_TRANSACTIONS = 'view_transactions';

    public const VIEW_CASHOUTS = 'view_cashouts';

    public const VIEW_STORED_EARNINGS = 'view_stored_earnings';

    public const VIEW_USERS = 'view_users';

    public const VIEW_CLIENTS = 'view_clients';

    public const VIEW_SERVICES = 'view_services';

    public const VIEW_CONTACT_MESSAGES = 'view_contact_messages';

    public const VIEW_CURRENCIES = 'view_currencies';

    public const VIEW_TRADING_CONTRACTS = 'view_trading_contracts';

    public const VIEW_TRADING_EARNINGS = 'view_trading_earnings';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::VIEW_POOL => 'Pool',
            self::VIEW_PLATFORM_HOLDINGS => 'Platform Holdings',
            self::VIEW_EARNINGS => 'Earnings',
            self::VIEW_TRANSACTIONS => 'Transactions',
            self::VIEW_CASHOUTS => 'Cashouts',
            self::VIEW_STORED_EARNINGS => 'Stored Earnings',
            self::VIEW_USERS => 'Users',
            self::VIEW_CLIENTS => 'Clients',
            self::VIEW_SERVICES => 'Services',
            self::VIEW_CONTACT_MESSAGES => 'Contact Messages',
            self::VIEW_CURRENCIES => 'Currencies',
            self::VIEW_TRADING_CONTRACTS => 'Trading Contracts',
            self::VIEW_TRADING_EARNINGS => 'Trading Earnings',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function groups(): array
    {
        return [
            'Finance' => [
                self::VIEW_POOL => self::labels()[self::VIEW_POOL],
                self::VIEW_PLATFORM_HOLDINGS => self::labels()[self::VIEW_PLATFORM_HOLDINGS],
            ],
            'Mining' => [
                self::VIEW_EARNINGS => self::labels()[self::VIEW_EARNINGS],
                self::VIEW_TRANSACTIONS => self::labels()[self::VIEW_TRANSACTIONS],
                self::VIEW_CASHOUTS => self::labels()[self::VIEW_CASHOUTS],
                self::VIEW_STORED_EARNINGS => self::labels()[self::VIEW_STORED_EARNINGS],
            ],
            'User Management' => [
                self::VIEW_USERS => self::labels()[self::VIEW_USERS],
                self::VIEW_CLIENTS => self::labels()[self::VIEW_CLIENTS],
            ],
            'Content' => [
                self::VIEW_SERVICES => self::labels()[self::VIEW_SERVICES],
                self::VIEW_CONTACT_MESSAGES => self::labels()[self::VIEW_CONTACT_MESSAGES],
            ],
            'Trading' => [
                self::VIEW_TRADING_CONTRACTS => self::labels()[self::VIEW_TRADING_CONTRACTS],
                self::VIEW_TRADING_EARNINGS => self::labels()[self::VIEW_TRADING_EARNINGS],
            ],
            'Settings' => [
                self::VIEW_CURRENCIES => self::labels()[self::VIEW_CURRENCIES],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::labels());
    }
}
