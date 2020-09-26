<?php

use Spatie\TemporaryDirectory\TemporaryDirectory;

beforeEach(function () {
    $this->temporaryDirectory = 'temporary_directory';
    $this->testingDirectory = __DIR__.DIRECTORY_SEPARATOR.'temp';
    $this->temporaryDirectoryFullPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->temporaryDirectory;

    deleteDirectory($this->testingDirectory);
    deleteDirectory($this->temporaryDirectoryFullPath);
});

it('can create a temporary directory', function () {
    $temporaryDirectory = (new TemporaryDirectory())->create();

    expect($temporaryDirectory->path())
        ->toBeDirectory();
});

it('can create a temporary directory with a name', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    expect($temporaryDirectory->path())
        ->and($this->temporaryDirectoryFullPath)
        ->toBeDirectory();
});

it('does not generate spaces in directory path', function () {
    $temporaryDirectory = (new TemporaryDirectory())->create();

    expect($temporaryDirectory->path())
        ->not->toContain(' ');
});

it('can create a temporary directory in a custom location', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->location($this->testingDirectory)
        ->name($this->temporaryDirectory)
        ->create();

    expect($temporaryDirectory->path())
        ->and($this->testingDirectory.DIRECTORY_SEPARATOR.$this->temporaryDirectory)
        ->toBeDirectory();
});

it('can create a temporary directory in a custom location through the constructor', function () {
    $temporaryDirectory = (new TemporaryDirectory($this->testingDirectory))
        ->name($this->temporaryDirectory)
        ->create();

    expect($temporaryDirectory->path())
        ->and($this->testingDirectory.DIRECTORY_SEPARATOR.$this->temporaryDirectory)
        ->toBeDirectory();
});

it('strips trailing slashes from a path', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $testingPath = $temporaryDirectory->path('testing'.DIRECTORY_SEPARATOR);

    expect($testingPath)
        ->not->toEndWith(DIRECTORY_SEPARATOR);
});

it('strips trailing slashes from a location', function () {
    $temporaryDirectory = (new TemporaryDirectory($this->testingDirectory.DIRECTORY_SEPARATOR))
        ->create();

    expect($temporaryDirectory->path())
        ->not->toEndWith(DIRECTORY_SEPARATOR);

    $temporaryDirectory = (new TemporaryDirectory())
        ->location($this->testingDirectory.DIRECTORY_SEPARATOR)
        ->create();

    expect($temporaryDirectory->path())
        ->not->toEndWith(DIRECTORY_SEPARATOR);
});

it('will not overwrite an existing directory by default', function () {
    mkdir($this->temporaryDirectoryFullPath);

    (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();
})->throws(InvalidArgumentException::class);

it('will overwrite an existing directory when using force create', function () {
    mkdir($this->temporaryDirectoryFullPath);
    $testFile = $this->temporaryDirectoryFullPath.DIRECTORY_SEPARATOR.'test.txt';
    touch($testFile);

    expect($testFile)
        ->toBeFile();

    (new TemporaryDirectory())
        ->force()
        ->name($this->temporaryDirectory)
        ->create();

    expect($this->temporaryDirectoryFullPath)
        ->toBeDirectory();

    expect($testFile)
        ->not->toBeFile();
});

it('provides chainable create methods', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    expect($temporaryDirectory)
        ->toBeInstanceOf(TemporaryDirectory::class);

    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->force()
        ->create();

    expect($temporaryDirectory)
        ->toBeInstanceOf(TemporaryDirectory::class);
});

it('can create a subdirectory in the temporary directory', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $subdirectory = 'abc';
    $subdirectoryPath = $temporaryDirectory->path($subdirectory);

    expect($subdirectoryPath)
        ->and("{$this->temporaryDirectoryFullPath}/{$subdirectory}")
        ->toBeDirectory();
});

it('can create a multiple subdirectories in the temporary directory', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $subdirectories = 'abc/123/xyz';
    $subdirectoryPath = $temporaryDirectory->path($subdirectories);

    expect($subdirectoryPath)
        ->and("{$this->temporaryDirectoryFullPath}/{$subdirectories}")
        ->toBeDirectory();
});

it('can create a path to a file in the temporary directory', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
    $subdirectoryFilePath = $temporaryDirectory->path($subdirectoriesWithFile);

    touch($subdirectoryFilePath);

    expect($subdirectoryFilePath)
        ->and("{$this->temporaryDirectoryFullPath}/{$subdirectoriesWithFile}")
        ->toBeFile();
});

it('can delete a temporary directory containing files', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
    $subdirectoryPath = $temporaryDirectory->path($subdirectoriesWithFile);

    touch($subdirectoryPath);

    $temporaryDirectory->delete();

    expect($this->temporaryDirectoryFullPath)
        ->not->toBeDirectory();
});

it('can delete a temporary directory containing no content', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $temporaryDirectory->delete();

    expect($this->temporaryDirectoryFullPath)
        ->not->toBeDirectory();
});

it('can delete a temporary directory containing broken symlink', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    symlink(
        $temporaryDirectory->path().DIRECTORY_SEPARATOR.'target',
        $temporaryDirectory->path().DIRECTORY_SEPARATOR.'link'
    );

    $temporaryDirectory->delete();

    expect($this->temporaryDirectoryFullPath)
        ->not->toBeDirectory();
});

it('can empty a temporary directory', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name($this->temporaryDirectory)
        ->create();

    $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
    $subdirectoryPath = $temporaryDirectory->path($subdirectoriesWithFile);

    touch($subdirectoryPath);

    $temporaryDirectory->empty();

    expect($this->temporaryDirectoryFullPath.DIRECTORY_SEPARATOR.$subdirectoriesWithFile)
        ->not->toBeFile();

    expect($this->temporaryDirectoryFullPath)
        ->toBeDirectory();
});

it('throws exception on invalid name', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->name('/');

})->throws(\Exception::class);

it('should return true on deleted file is not existed', function () {
    $temporaryDirectory = (new TemporaryDirectory())
        ->delete();

    expect($temporaryDirectory)
        ->toBeTrue();
});
