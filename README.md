# Slack Newsletter

This application allows you to generate a newsletter from your [Slack](https://slack.com) channels. 
It goes through the channels looking for links, combines them into a html file, and sends it by email.
It's ideal for keeping track of your finds when the historical Slack reaches its limit.

## Installation & Configuration

1. Clone the project and launch `composer install` inside.

2. Generate a [Token on your slack workspace](https://api.slack.com/custom-integrations/legacy-tokens) - _Currently only tested with legacy token_

3. In your `.env`, complete your smtp configuration and add your Slack token like that :

```
SLACK_TOKEN=xoxp-XXXXXXXXX-XXXXXXX-XXXXXXXXX
```

4. Choose your channels to browse and add them in `config/channels.json`.
You can check out the `config/channels.json.dist` to see how add a new channels

5. Add receivers for your newsletter in `config/receivers.json`

6. (OPTIONNAL) : Pimp your newsletter by altering parameters in `config/package/parameters.yaml`

7. Configure your cron to execute command to browse, build, and send newsletter. 

For example :

```bash
## Every day at 8am, browse channels and store them
0 8 * * * php bin/console app:newsletter:browse
## Every monday at 8:05 am, build the newsletter and send it
5 8 * * 1 php bin/console app:newsletter:build && php bin/console app:newsletter:send
```

## Build With

* [Symfony 4.0](http://symfony.com/)
    * symfony/flex
    * symfony/console
    * symfony/swiftmailer-bundle
    * symfony/yaml
* [FlySystem from The Php League](http://flysystem.thephpleague.com/)
* [Frlnc Php Slack](https://github.com/Frlnc/php-slack)
* [Embed](https://github.com/oscarotero/Embed)
* [Carbon](https://carbon.nesbot.com/)

Thank to theirs awesome work. 

## Customize the newsletter

If you want to customize the newsletter, all templates are in `templates` folder.

Before testing rendering, you have to retrieve some messages :

```bash
php bin/console app:newsletter:browse -d 5
```
> The `-d` or `--days` is to specified how many days to retrieve. 
You can have lots of data by this way.

To test the view in web-browser, launch the built-in web server :

```bash
php bin/console server:run
```
and go to [http://127.0.0.1:8000/test/mail](http://127.0.0.1:8000/test/mail)

But you probably have to send emails to test compatibility with emails viewer.

You can launch theses commands to avoid archivation of messages and builded news.

```bash
php bin/console app:newsletter:build --no-archive
php bin/console app:newsletter:send --no-archive
```

## Contribute

First of all, thank you for contributing ♥

If you find any typo/misconfiguration/... please send me a PR or open an issue.

Also, while creating your PR, please write a description which gives the context and/or explains why you are creating it.


## TODOs

- [x] Make installation as simple as a `composer create-project barth/slacknewsletter`
- [x] Browse private channel
- [ ] Write Tests Suite
- [ ] Make sure it'll work with [Slack App](https://api.slack.com/apps) and provide a configuration guide
- [ ] Add translations
- [ ] Easily extend with other Team Collaboration Software (HipChat...)



