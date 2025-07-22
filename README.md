TO DO
THIS IS WIP page

Requirement Expectation

phpcs --standard=WordPress ✅ Filenames like class-admin.php
Composer PSR-4 Autoloading ✅ Filenames like Admin.php

Solution
Exclude filename stan from phpcs validation

```bash
phpcs --standard=WordPress --ignore=vendor,node_modules --exclude=WordPress.Files.FileName
```
