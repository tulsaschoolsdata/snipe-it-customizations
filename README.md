# Snipe-IT Customizations for Tulsa Public Schools

- [The Customizations](#the-customizations)
  - [Restore a Backup](#restore-a-backup)
  - [Authenticate with Microsoft Graph](#authenticate-with-microsoft-graph)
- [Environment Variables](#environment-variables)

## The Customizations

This library extends a [Snipe-IT] application in the following ways:

### Restore a Backup

Adds a new artisan commmand `snipeit:backup-restore`. This corresponds to the existing `snipeit:backup` command.

See the [Override Hacks] section about overriding [config/backup.php] and [config/filesystems.php].

```console
$ php artisan snipeit:backup-restore

 Choose a backup [Snipe-IT]:
  [0] Snipe-IT
 >

 Choose a version [snipe-it-2020-07-07-18-15-38.zip]:
  [0] snipe-it-2020-07-07-18-15-38.zip
  [1] snipe-it-2020-07-02-09-58-47.zip
  [2] snipe-it-2020-06-23-18-18-22.zip
  [3] snipe-it-2020-06-23-14-23-11.zip
 >

Loading backup: Snipe-IT/snipe-it-2020-07-07-18-15-38.zip
Restoring:
- db-dumps/mysql-snipeit.sql
- var/www/html/storage/oauth-private.key
- var/www/html/storage/oauth-public.key
- ...
- var/www/html/config/mail.php
- var/www/html/config/trustedproxy.php
```

### Authenticate with Microsoft Graph

Enables registering and logging in with TPS’ Microsoft tenant through the Graph API. Leverages the [Microsoft Graph Socialite Provider] for the integration.

Visit http://localhost:3051/login/graph to login.
> `TODO: Customize the login page with a Login with Microsoft button or link.`

## Environment Variables

Several environment variables are required to function correctly.

- `BACKUPS_AWS_KEY`: Standard AWS_ACCESS_KEY_ID
- `BACKUPS_AWS_SECRET`: Standard AWS_SECRET_ACCESS_KEY
- `BACKUPS_AWS_REGION`: AWS Region
- `BACKUPS_AWS_BUCKET`: AWS Bucket
- `BACKUPS_AWS_PREFIX`: Optional path prefix within bucket
- `BACKUPS_AWS_URL`: Optional endpoint for using AWS alternatives like Minio
- `GRAPH_KEY`: Application (client) ID or Application ID URI
  - *example: `https://tulsaschools.onmicrosoft.com/snipe-it`*
- `GRAPH_SECRET`: Client secret for the registered app
- `GRAPH_REDIRECT_URI`: Redirect URI for the registered app
  - *example: `http://localhost:3051/login/graph/callback`*
- `GRAPH_TENANT_ID`: Directory (tenant) ID
  - *example: `tulsaschools.onmicrosoft.com`*

[Microsoft Graph Socialite Provider]: https://socialiteproviders.netlify.app/providers/microsoft-graph.html
[Snipe-IT]: https://github.com/snipe/snipe-it
