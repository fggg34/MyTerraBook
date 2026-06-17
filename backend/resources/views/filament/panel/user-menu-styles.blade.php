<style>
    .fi-sidebar .fi-user-menu-trigger {
        border: 1px solid rgb(255 255 255 / 0.12);
        background: rgb(255 255 255 / 0.06);
        cursor: pointer;
    }

    .dark .fi-sidebar .fi-user-menu-trigger {
        border-color: rgb(255 255 255 / 0.12);
        background: rgb(31 41 55 / 0.6);
    }

    .fi-sidebar .fi-user-menu-trigger:hover,
    .fi-sidebar .fi-user-menu-trigger:focus-visible {
        border-color: rgb(255 255 255 / 0.25);
        background: rgb(255 255 255 / 0.12);
    }

    .dark .fi-sidebar .fi-user-menu-trigger:hover,
    .dark .fi-sidebar .fi-user-menu-trigger:focus-visible {
        border-color: rgb(255 255 255 / 0.2);
        background: rgb(255 255 255 / 0.08);
    }

    .fi-sidebar .fi-user-menu-trigger-text {
        display: grid;
        min-width: 0;
        flex: 1;
        justify-items: start;
        gap: 0.125rem;
        text-align: start;
    }

    .fi-sidebar .fi-user-menu-trigger-label {
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        color: rgb(255 255 255 / 0.92);
    }

    .dark .fi-sidebar .fi-user-menu-trigger-label {
        color: rgb(243 244 246 / 1);
    }

    .fi-sidebar .fi-user-menu-trigger-sublabel {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
        font-size: 0.6875rem;
        font-weight: 500;
        line-height: 1.2;
        color: rgb(255 255 255 / 0.55);
    }

    .dark .fi-sidebar .fi-user-menu-trigger-sublabel {
        color: rgb(156 163 175 / 1);
    }

    .fi-sidebar .fi-user-menu-collapsed-settings-icon {
        position: absolute;
        right: -0.125rem;
        bottom: -0.125rem;
        display: flex;
        height: 1rem;
        width: 1rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid rgb(255 255 255 / 0.15);
        background: var(--mtb-primary-dark, #243b53);
        color: rgb(255 255 255 / 0.7);
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.2);
    }

    .fi-sidebar .fi-user-menu-collapsed-settings-icon .fi-icon {
        width: 0.625rem;
        height: 0.625rem;
    }

    .dark .fi-sidebar .fi-user-menu-collapsed-settings-icon {
        border-color: rgb(255 255 255 / 0.12);
        background: rgb(31 41 55 / 1);
        color: rgb(209 213 219 / 1);
    }

    .fi-sidebar .fi-user-menu-trigger .fi-user-avatar {
        position: relative;
    }

    .fi-user-menu-account-info {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .dark .fi-user-menu-account-info {
        border-bottom-color: rgb(255 255 255 / 0.08);
    }

    .fi-user-menu-account-info-name {
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.875rem;
        font-weight: 600;
        color: rgb(17 24 39 / 1);
    }

    .dark .fi-user-menu-account-info-name {
        color: rgb(243 244 246 / 1);
    }

    .fi-user-menu-account-info-email {
        margin: 0.125rem 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.75rem;
        color: rgb(107 114 128 / 1);
    }

    .dark .fi-user-menu-account-info-email {
        color: rgb(156 163 175 / 1);
    }
</style>
