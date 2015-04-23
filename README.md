# Corouser 


[![Build Status](https://travis-ci.org/zavalit/corouser.svg)](https://travis-ci.org/zavalit/corouser)

## Coroutine based server written in php

#### Preview

Inspired by awesome [nikic aticle](https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html), and is developed mostly for sake of learing a concept of coroutine in php 


#### Install it per Composer

    composer require --dev zavalit/corouser:dev-master
    
##### and boot it

    vendor/bin/server 8081 
    
  In example above **8081** is your port number and you are shurely free to choose any other port you wish
  
  
#### Install it and boot it per Docker
  It can be obviously the case that you don't have php version >=5.5, in that case you can run it simply within docker container that has it.
  
    #get the code
    git clone https://github.com/zavalit/corouser.git
    
    #go to the source code
    cd corouser
    
    #and run a container
    docker run -d -p 8081:8081 -v $PWD:/var/www  zavalit/corouser
    
#### And simply call it 
in a browser

    http://localhost:8081
     
or benchmark it

    ab -n 10000 -c 1000 http://0.0.0.0:8081/    
