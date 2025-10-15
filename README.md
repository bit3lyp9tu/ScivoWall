# ScaDS poster generator (tbd)

![Current build status](https://github.com/bit3lyp9tu/scientific_poster_generator/actions/workflows/main.yml/badge.svg?event=push)

Idea is to have a centralized poster generator, which allows visual editing of
scientific poster with LaTeX, images, lists and so on (almost full markdown support).

# Setup
To setup the container environment, you have to run the folowing command:
```console
bash docker.sh --local-port 1112
```
The ```--local-port``` parameter indicates which network port the server is using. This parameter is strictly required for the setup command to work.

Using the ```--showcase``` parameter will load a database with already preconfigured posters.

If you want to know if everything is setup correctly, optionally you can use:
```console
bash docker.sh --local-port 1112 --run-tests
```

After executing the setup script you can access the page in a browser of your choice at ```http://localhost:1112/```.

For further information please refer to the [User Manual](documentation/docu.md).

# Copyright

- ```/img/icons/instruction_manual.svg``` from ```[here](https://commons.wikimedia.org/wiki/File:ISO_7000_-_Ref-No_1641.svg)```

Creative Commons Attribution-Share Alike 4.0 International license:
- ```/img/icons/Loading_icon.gif``` from [here](https://commons.wikimedia.org/wiki/File:Loading_icon.gif#/media/File:Loading_icon.gif)

MIT licence:
- ```/img/icons/Icons8_flat_delete_generic.svg``` from [here](https://commons.wikimedia.org/wiki/File:Icons8_flat_delete_generic.svg)
- ```/img/icons/Icons8_flat_opened_folder.svg``` from [here](https://commons.wikimedia.org/wiki/File:Icons8_flat_opened_folder.svg)
- ```/img/icons/Edit_Notepad_Icon.svg``` from [here](https://commons.wikimedia.org/wiki/File:Edit_Notepad_Icon.svg)

Copyright © *Icons8 2016*

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
The Software is provided "as is", without warranty of any kind, express or implied, including but not limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. In no event shall the authors or copyright holders be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the Software or the use or other dealings in the Software.
