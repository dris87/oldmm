Here are some external libraries, 
which we use but for some reason could not get to work thourgh npm.


### jquery-easy-loading

For some reason after compile, the browser console gave error that module '.' is not defined.
After changing this:
```
    define([jquery, window], factory);
```
to this:
```
    define(factory, [jquery, window]);
```
it works.