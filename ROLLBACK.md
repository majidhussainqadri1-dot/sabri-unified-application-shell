# Rollback

The plugin captures an activation snapshot before defaults or migrations mutate shell settings.

Rollback may restore only:

- shell settings;
- shell navigation mappings;
- shell front-page-related configuration stored by the shell;
- shell theme-visibility settings.

Rollback must not delete or modify:

- posts;
- pages;
- users;
- media;
- comments;
- messages;
- appointments;
- marketplace data;
- clinic data;
- companion-plugin tables.

## Admin Rollback

1. Open **Sabri Shell > Repair**.
2. Select **Rollback Shell Settings**.
3. Review **System Check**.
4. Clear page cache if the site uses a cache plugin.

## Emergency Constant

If wp-admin is not practical, a developer can temporarily add:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

Remove the constant after the shell has been repaired or reconfigured.
