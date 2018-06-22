
## what it does

This package creates the typo3 cli command `slimdump:run`.

This command is preconfigured with the database credentials of your typo3 instance the command is run in.

It is also preconfigured to exclude cache and session tables. It will search for slimdump config files in all extensions under `EXT:*/Resources/Private/Slimdump/default.xml`. That's also the way you can easily add more configurations. If you want more presets, you can run `slimdump:run` with `--config minimal` wich will load `EXT:*/Resources/Private/Slimdump/minimal.xml` or you can specify a complete path like `--config path/to/my/config.xml`.

Since slimdump accepts multiple config files, you can add multiple too by seperating them with a comma `--config minimal,remove-personal-data,path/to/another/config.xml`.

## how to install

`composer require nemo64/slimdump`. There is no other way planned at the moment.