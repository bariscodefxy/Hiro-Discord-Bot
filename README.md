## Hiro Discord Bot
A Discord bot written in PHP.
### Why Hiro Bot?
- 100% Open-source & Secure
- Bot is using PHP language
  - PHP is fast learnable programming language that most using in the Web, so you can learn in average 1 week and you can fork this project and you can make your own bot
- We're fast merging your good pull requests
- Most features in one bot
- Has multi language support.
### Languages (Translations)
- English %100
- Japanese %100
- Turkish %100
- Korean %10
### Features
- Can make your server more online with RPG commands
  - You can set RPG channel that RPG commands only usable in there
  - RPG system is very funny, members of your server will love that
- Can play songs on YouTube in your voice channels
- For anime lovers bot has commands
  - $waifu - Sends random waifu image
  - $hug - You can hug a member, bot will send anime hugging gif with a message
  - $kiss - You can kiss a member, bot will send anime kissing gif with a message
  - $marry - You can marry with a member, bot will send anime marrying gif with a message (It is won't store in database)
  - $nekos - Just like waifu command but it's uses another API
  - $slap - You can slap a member, bot will send anime slapping gif with a message
- Bot has economy system
  - $coinflip - A gambling game that you have 1/2 chance
  - $daily - You can get daily earnings
  - $money - You can see your current money
  - $pay - You can give money to someone
  - $slots - A gambling game that you know as slot machine
- Bot also has some moderation commands
  - $ban - Bans a member from your guild
  - $clear - Deletes as many messages as you type
  - $kick - Kicks a mebmer from your guild
  - $nick - Changes or resets member display name

### Creating your bot from fork Hiro016 bot
#### Your Or PC/VPS
##### Install Bot
###### Clone Repository to your PC/VPS
```
git clone https://github.com/bariscodefxy/Hiro-Discord-Bot.git
```
###### Manual Download
- Download from Code button
- Extract to anywhere
###### Setup
- Install PHP 8 or more
- Install Composer
- Make file with name `.env`
- Put your token from Discord Developer Portal 
to your .env file like this: `TOKEN=YOUR_BOT_TOKEN`
- Put this command to your cmd/terminal: `composer update` or `composer install`
###### Running
- `php ./bot.php`
Done

### Heroku
Fork this project and sign in on Heroku then create new
Heroku project then select your repository on your project
then select heroku/php module, make new environment named TOKEN and
put your token there from Heroku Settings.
Done!

### License
```
Copyright © 2025-present bariscodefx

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
