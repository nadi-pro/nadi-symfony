<?php

namespace Nadi\Symfony\Data;

use Nadi\Data\Entry as DataEntry;
use Nadi\Symfony\Concerns\InteractsWithMetric;

class Entry extends DataEntry
{
    use InteractsWithMetric;

    public $user;

    public function __construct($type, array $content, $uuid = null)
    {
        parent::__construct($type, $content, $uuid);
        $this->registerMetrics();
    }

    public function user($user): static
    {
        $this->user = $user;

        $id = method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : ($user->getId() ?? null);
        $name = method_exists($user, 'getUsername') ? $user->getUsername() : (string) $user;
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : null;

        $this->content = array_merge($this->content, [
            'user' => [
                'id' => $id,
                'name' => $name,
                'email' => $email,
            ],
        ]);

        $this->tags(['Auth:'.$id]);

        return $this;
    }
}
