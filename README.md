# ScaDS poster generator (tbd)

![Current build status](https://github.com/bit3lyp9tu/scientific_poster_generator/actions/workflows/main.yml/badge.svg?event=push)

Idea is to have a centralized poster generator, which allows visual editing of
scientific poster with LaTeX, images, lists and so on (almost full markdown support).

# CAVEAT

#### Setup

To setup the container environment, you have to run the folowing command:
```console
bash docker.sh --local-port 1112
```
The ```--local-port``` parameter indicates which network port the server is using. This parameter is strictly required for the setup command to work.


If you want to know if everything is setup correctly, optionally you can use:
```console
bash docker.sh --local-port 1112 --run-tests
```
#### Admin User

#### Index-Page
#### Projects-Page
#### Poster-Page

## Upload
The user can upload several types of content into a box. For images the supported file types are:
- png
- jpg
- gif

Additionally the user can upload charts of the following file types:
- csv (for rendering simple charts)
After uploading the file it should look like this:
```console
<p placeholder="plotly" chart="scatter">
x,y
1,2
3,2
3,6
0,3
-2,4
3,3
</p>
```
Valid chart types are: ```scatter```, ```line```, ```bar```, ```pie```
To modify the chart type, replace the chart type with one of the other types e.g.: ```chart="bar"```

- json (for rendering more complex charts, high customization potential; see path: ```/plotly/examples/*.json```, For some reason some won't work :( Issue: #203; #204)

To upload, hover over the Box you want add to content to and click on the [```Browse...```] button. Now select your file. Now the content of your file should be inside a placeholder, you can click outside of your selected Box.

Now, the text with the placeholder should disappear and the content should be correctly redered.
If not please check if the file content has the correct formating.
