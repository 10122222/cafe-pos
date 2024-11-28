<?php

namespace Database\Seeders;

use App\Filament\Resources\OrderResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Closure;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL . 'Creating super admin user...');
        $user = $this->withProgressBar(1, fn () => User::factory(1)->create([
            'name' => 'super admin',
            'email' => 'super.admin@cafe.pos',
        ]));
        $this->command->info('Super admin user created.');

        $this->command->warn(PHP_EOL . 'Creating categories...');
        $categories = $this->withProgressBar(20, fn () => Category::factory(1)->create());
        $this->command->info('Categories created.');

        $this->command->warn(PHP_EOL . 'Creating products...');
        $products = $this->withProgressBar(100, fn () => Product::factory(1)
            ->sequence(fn ($sequence) => ['category_id' => $categories->random(1)->first()->id])
            ->create());
        $this->command->info('Products created.');

        $this->command->warn(PHP_EOL . 'Creating customers...');
        $customers = $this->withProgressBar(100, fn () => Customer::factory(1)->create());
        $this->command->info('Customers created.');

        $this->command->warn(PHP_EOL . 'Creating orders...');
        $orders = $this->withProgressBar(100, fn () => Order::factory(1)
            ->sequence(fn ($sequence) => ['customer_id' => $customers->random(1)->first()->id])
            ->has(Payment::factory()->count(1))
            ->has(
                OrderItem::factory()->count(rand(2, 5))
                    ->state(fn (array $attributes, Order $order) => ['product_id' => $products->random(1)->first()->id]),
                'items'
            )
            ->create()::each(function ($order) {
                $totalPrice = $order->items->sum(function ($item) {
                    return $item->qty * $item->unit_price;
                });
                $order->update(['total_price' => $totalPrice]);
            }));

        foreach ($orders->random(rand(5, 8)) as $order) {
            Notification::make()
                ->title('New order')
                ->icon('heroicon-o-shopping-bag')
                ->body("{$order->customer->name} ordered {$order->items->count()} products.")
                ->actions([
                    Action::make('View')
                        ->url(OrderResource::getUrl('edit', ['record' => $order])),
                ])
                ->sendToDatabase($user);
        }
        $this->command->info('Orders created.');

        $this->call([
            ShieldSeeder::class,
        ]);
        $user->first()->assignRole('super_admin');
    }

    protected function withProgressBar(int $amount, Closure $createCollectionOfOne): Collection
    {
        $progressBar = new ProgressBar($this->command->getOutput(), $amount);

        $progressBar->start();

        $items = new Collection;

        foreach (range(1, $amount) as $i) {
            $items = $items->merge(
                $createCollectionOfOne()
            );
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->command->getOutput()->writeln('');

        return $items;
    }
}
