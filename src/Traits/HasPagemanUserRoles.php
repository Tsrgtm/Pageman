<?php

namespace App\Traits\Pageman; // This is the target namespace after publishing

trait HasPagemanUserRoles
{
    /**
     * Check if the user has a specific role.
     * Assumes your User model has a 'role' property.
     *
     * @param string $roleToCheck
     * @return bool
     */
    public function hasRole(string $roleToCheck): bool
    {
        return property_exists($this, 'role') && $this->role === $roleToCheck;
    }

    /**
     * Check if the user is an administrator.
     * Uses 'pageman.admin_role_name' from Pageman's configuration,
     * defaulting to 'admin'.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(config('pageman.admin_role_name', 'admin'));
    }

    /**
     * Check if the user can access the Pageman admin panel.
     * This is the primary method Pageman will use.
     *
     * @return bool
     */
    public function canAccessPagemanAdmin(): bool
    {
        // You can add more complex logic here if needed,
        // e.g., checking multiple roles or specific permissions.
        return $this->isAdmin();
    }
}