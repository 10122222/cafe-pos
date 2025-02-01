<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Components\OrderForm;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected ?string $currentStep = null;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false)
                    ->persistStepInQueryString(),
            ])
            ->columns(null);
    }

    protected function afterCreate(): void
    {
        /** @var Order $order */
        $order = $this->record;

        /** @var int $total_price */
        $total_price = $order->items->sum(function ($item) {
            return $item->qty * $item->unit_price;
        });

        $order->update(['total_price' => $total_price]);

        /** @var User $user */
        $user = auth()->guard()->user();

        /** @var Customer $customer */
        $customer = $order->customer;

        Notification::make()
            ->title(__('resources/order.notifications.new.title'))
            ->icon('heroicon-o-shopping-bag')
            ->body(
                '**' . __('resources/order.notifications.new.body', [
                    'customer' => $customer->name,
                    'count' => $order->items->count(),
                ]) . '**'
            )
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase($user);
    }

    /** @return Step[] */
    protected function getSteps(): array
    {
        return [
            Step::make('Details')
                ->icon('heroicon-o-identification')
                ->schema([
                    Section::make()->schema(OrderForm::getDetailsFormSchema())->columns(),
                ]),

            Step::make('Items')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    Section::make()->schema([
                        OrderForm::getItemsRepeater(),
                    ]),
                ]),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
