# TMU Core

## Development Notes

To develop the core package in the context of a full Laravel app, we utilize 
composer's ability to define a [local path repository](https://getcomposer.org/doc/05-repositories.md#path). When a repository with a local path is defined, a 
symlink is created from the path to the project's `/vendor` directory.

**NOTE: On windows, you must choose `Run As Administrator` when starting up your terminal / VM. You must do this _before_ running `vagrant up` as virtualbox needs administrator permissions to make windows-compatible symlinks work. If you have previously ran `composer update`, you'll need to clear the `/vendor` folder and re-run it after starting the VM with administrator permissions.**