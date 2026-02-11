<?php

namespace App\Livewire;

use App\Models\User;
use App\Notifications\QueuedWelcomeNotification;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EditUserComponent extends Component
{
    public User $user;

    public ?string $email;

    public ?string $name;

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', Rule::unique('users', 'email')->ignore($this->user->id)],
            'name' => ['required', 'string'],
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->fill($this->user->toArray());
    }

    public function save(): void
    {
        $this->user->email = $this->email;
        $this->user->name = $this->name;
        $this->user->save();

        notify(__mc('The user has been updated.'));
    }

    public function resendInvitation(): void
    {
        $this->user->notify(new QueuedWelcomeNotification(now()->addDay()));

        notify(__mc('An invitation mail will be sent to :email.', ['email' => $this->user->email]));
    }

    public function render()
    {
        return view('livewire.users.edit')
            ->layout('mailcoach::app.layouts.settings', ['title' => $this->user->name]);
    }
}
