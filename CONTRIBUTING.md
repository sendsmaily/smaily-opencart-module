First off, thanks for taking the time to contribute!

# Table of contents

- [Getting started](#getting-started)
- [Internals](#internals)
    - [Structure of the repository](#structure-of-the-repository)
    - [Modman](#modman)
        - [Updating links](#updating-links)
- [Development](#development)
    - [Starting the environment](#starting-the-environment)
    - [Stopping the environment](#stopping-the-environment)
    - [Resetting the environment](#resetting-the-environment)


# Getting started

The development environment requires [Docker](https://docs.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) to run.
Please refer to the official documentation for step-by-step installation guide.

Clone the repository:

    $ git clone git@github.com:sendsmaily/smaily-opencart-module.git

Next, change your working directory to the local repository, and checkout `1.5.x` master branch:

    $ cd smaily-opencart-module
    $ git checkout 1.5.x

And run the environment:

    $ docker-compose up


# Internals

## Structure of the repository

The repository is split into multiple parts:

- `assets` - screenshots for plugin page;
- `upload` - module files;

In addition there are system directories:

- `.github` - GitHub issue and pull request templates;
- `.sandbox` - files needed for running the development environment;

## Modman

Due to the structure limitations of OpenCart and not having the possibility merge mounts in Docker we use
[Modman](https://github.com/colinmollenhour/modman) in our development environment to make sure that module files are mounted in the
correct directory in the container.

Modman files are automatically linked when container is started, but not when you add or remove files (or directories) from the module.

### Updating links

First you need to update list of files in `upload/modman`.

And then in terminal run:

    $ docker exec smaily-opencart-module_app_1 modman deploy smaily_for_opencart


# Development

## Starting the environment

You can run the environment by executing:

    $ docker-compose up

> **Note!** Make sure you do not have any other process(es) listening on ports 8080 and 8888.

## Stopping the environment

Environment can be stopped by executing:

    $ docker-compose down

## Resetting the environment

If you need to reset the installation, just simply delete environment's Docker volumes. Easiest way to achieve this is by running:

    $ docker-compose down -v