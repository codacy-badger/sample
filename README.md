# Jobayan
The Best Job Site Ever!

## Cradle Documentation

 - See [Kitchen Sink](https://cradlephp.github.io/docs/sink.html) for instructions on how to install.
 - See [https://cradlephp.github.io/](https://cradlephp.github.io/) for the official documentation.

## Packages

 - Also see [https://github.com/cblanquera/cradle-csrf](https://github.com/cblanquera/cradle-csrf)
 - Also see [https://github.com/cblanquera/cradle-captcha](https://github.com/cblanquera/cradle-captcha)
 - Also see [https://github.com/cblanquera/cradle-queue](https://github.com/cblanquera/cradle-queue)
 - Also see [https://github.com/cblanquera/cradle-handlebars](https://github.com/cblanquera/cradle-handlebars)
 
 ## Creating Pull Request
 
 ## Environment
 - rabbitmq
 - elasticsearch 5.6.*
 - redis
 - php 7.2
 - mysql 5.7
 - supervisor
 
 ## Tools
 - npm
 - composer
 - bower
 - bootstrap 3.7
 
 ## Handlebars Cache
 - go to root folder
 - mkdir cachedTpl && chmod -R 777 cachedTpl
 
 ## Stage Servers
 - stage.jobayan.com
 - stage2.jobayan.com
 - stage3.jobayan.com
  
 ## workers
 ##### running on screen
  - bin/cradle faucet work jobayan_terms -v --mode exec
  - bin/cradle faucet work jobayan -v --mode exec
 ##### supervisord
 - service supervisord stop
 - service supervisord start
   
 ## Timezone
 #### How to set timezone
-  rm -rf /etc/localtime
-  ln -s /usr/share/zoneinfo/Asia/Manila /etc/localtime

## Migration
- MySql, after importing dump file to new database
- run `optimize table post`;