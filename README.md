# Corouser 



## Coroutine based server written in php

#### Preview

Inspired by awesome [nikic aticle](https://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html), and is developed mostly for sake of learing a concept of coroutine in php 


#### Install it per Composer

    composer require --dev zavalit/corouser:dev-mamster
    
##### and boot it

    vendor/bin/server 8081 
    
  In example above **8081** is your port number and you are shurely free to choose any other port you wish
  
  
#### Install it and boot it per Docker
  It can be obviously the case that you don't have php version >=5.5, in that case you can run it simply within docker container that has it.
  
    #get the code
    git clone https://github.com/zavalit/corouser.git
    
    #go to Dockerfile
    cd corouser/docker
    
    #build an image
    docker build -t corouser/server .
    
    #get back to project root
    cd ..
    
    #and run a container
    docker run -d -p 8081:8081 -v $PWD:/var/www  corouser/server
    
#### And finaly run it

    http://localhost:8081
     