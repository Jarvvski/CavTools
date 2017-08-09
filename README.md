![Imgur](http://i.imgur.com/TbKz8R9.jpg)

# Welcome

7Cav Gaming Xenforo general purpose addon. Currently on v2.1

## Summary:

- S3 event management
- RRD enlistment processor and manager
- Automated XML
- Automated S2 quarterly check
- Several QoL fixes

# Developers:

## Setting up dev env

First you need to have docker installed for your respective system

- [macOS](https://docs.docker.com/docker-for-mac/)
- [Windows](https://docs.docker.com/docker-for-windows/)
- [Linux](https://docs.docker.com/engine/installation/linux/ubuntu/)

Find out more about the initial setup of docker [here](https://docs.docker.com/get-started/), however IMO has worked to make this as seamless as possible.

Just clone this repo to your dev machine, and work in the `js` and `library` directories as normal for your code.

To quickly add a domain to your `/etc/hosts` to make life easier:

```
sudo echo "127.0.0.1  cav.dev" >> /etc/hosts
```

**I don't know the windows equivalent**

> Note: you can use port 3306 to access the db in a client of your choosing

## Running the system

To start the app, run:

```
docker-compose up
```

> Add the `-d` flag for a daemon to run in the background

This will take a few seconds depending on if this is your first time running the environment. Give it some time. Eventually, navigate to `cav.dev` in your browser, or 127.0.0.1 if you didn't add it to your `/etc/hosts`. The app should be running as normal, and you now have the ability to live edit code in the `js` and `library` directories as expected.

To turn off the app, run:

```
docker-compose down
```

This will stop the app, but will retain any changes you made to the database and XenForo application.

## FAQ

Contact [Jarvis](https://7cav.us/members/jarvis-a.13/) on the Cav Discord for any issues you come across.
