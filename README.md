# ToggleButtonsColumn

Like Filament's ToggleButtons, but for table columns.

## Installation

You can install the package via composer:

```bash
composer require charlieetienne/toggle-buttons-column
```

## Usage

Use ToggleButtonsColumn in your Filament tables:

```php
use CharlieEtienne\ToggleButtonsColumn\ToggleButtonsColumn;

ToggleButtonsColumn::make('status')
    ->options([
        'unsigned' => 'Unsigned',
        'present' => 'Present',
        'absent' => 'Absent',
    ])
    ->icons([
        'unsigned' => Heroicon::Clock,
        'present' => Heroicon::Check,
        'absent' => Heroicon::XMark,
    ])
    ->colors([
        'unsigned' => 'warning',
        'present' => 'success',
        'absent' => 'danger',
    ]),
```

### Using enums

Like Filament's ToggleButtons, you can use enums to define your options:

```php
    ToggleButtonsColumn::make('status')
        ->options(OrderStatus::class)
```

### Grouped toggle buttons

Like Filament's ToggleButtons form component, you can group your options using the `grouped()` method:

```php
    ToggleButtonsColumn::make('status')
        ->grouped()
        ->options(OrderStatus::class)
```

### Options

This column accepts all the same styling options as Filament's ToggleButtons, except for `inline`.

For example, you can use `boolean()`, `grouped()`, etc.

Additionally, you have access to all the editable column methods like `getStateUsing()`, `updateStateUsing()`, etc.

## Requirements

- PHP 8.2+
- Filament 4.0+
- Laravel 11.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- Built for [Filament](https://filamentphp.com)
- Created by [Charlie Etienne](https://github.com/charlieetienne)
