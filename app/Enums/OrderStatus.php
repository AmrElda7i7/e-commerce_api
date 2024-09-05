<?php

namespace App\Enums;

enum OrderStatus : int
{
    case CASH_ON_DELIVERY = 0;
    case ONLINE_PAYMENT = 1;

    public static function search(string $value): ?OrderStatus
    {
        $values = [
            'cash_on_delivery' => self::CASH_ON_DELIVERY,
            'online_payment' => self::ONLINE_PAYMENT,
        ];

        return $values[$value] ?? null;
    }
    
    public function name(): string
    {
        return match ($this) {
            self::CASH_ON_DELIVERY => 'cash_on_delivery',
            self::ONLINE_PAYMENT => 'online_payment',
        };
    }
    // public static function getKeyByValue(int $value): string
    // {
    //     foreach (self::cases() as $case) {
    //         if ($case->value === $value) {
    //             return $case->name();
    //         }
    //     }

    //     throw new \InvalidArgumentException("Invalid value: $value");
    // }
}
