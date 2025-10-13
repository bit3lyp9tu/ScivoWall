# User Manual

# Index-Page
For a poster to be shown on the index-page, two requrments need to be meed:
1. The poster needs to be set from ```private``` to ```public``` *(by admin, user)*
2. The poster needs to be set to ```visible = true```, using the checkbox *(only by admin)*

# Projects-Page
The projects-page is the page where all the posters are listed.
There exists the following three tables:
- Posters
- Authors
- Images

To create a new Poster the user has to fill out the form with a new name and click on the <img src="/documentation/img/create_new_project.png" width="85" style="position: relative; top: 4px;"/> Button.
Note: *By Default the poster title is automatically reformated into a Markdown headline by adding ```# ``` as a prefix.*

![](documentation/img/projects_new.png)

New Authors and Images are automaticaly added to the tables when they are included on the Poster-Page.
Those tables can be used to manage e.g. excessive data.

The Admin hase some additional privileges to manage all existing Posters.

## Admin-User
For the public poster to be depicted on the index-page the admin is requred to set the poster to visible (tick the checkbox).

![](documentation/img/poster_visibility_small.png)

The Admin has access to all posters and can modify them.
On default all available posters will be shown on the Projects-Page. To limit the number of posters shown or to serach for a specific group of posters, the admin can use the filter menu:

![](documentation/img/poster_filter_menu.png)

# Poster-Page
The poster-page constists of the following elements:

Title
: The Poster Title, describes how the poster is named. Can be edited and supports Markdown and LaTeX *(see below)*.

Authors
: The Authors of the poster, to add a new author write in the textfield and deselect the textfield.
If authors are already added, they will be shown in the autocomplete options.
To remove an author, hover on the author element and click on the <img src="/img/icons/Icons8_flat_delete_generic.svg" width="25" style="position: relative; top: 10px;"/>-Button.
The author order can be changed via drag and drop.

view_mode
: Can be ```private```*(default)* or ```public```. If on ```private``` poster con not be shown on index-page.

Boxes (Text fields)
: Contain the actual text content of the poster.
A Box can have two modes: Selected- or Unselected-Mode *(default)*.
In the unselected mode, the text content gets rendered (Markdown, LaTeX, Images, Charts).
To change the the content the user has to click on the box and the mode gets switched to selected.
To add a new Box, click on the <img src="/documentation/img/add_box.png" width="55" style="position: relative; top: 5px;"/> button.
To remove a Box remove all text from the Box and deselect the textfield.
To edit a Box, click on the Box, the user can now edit the textfield, deselect said textfield afterwards or press *Ctrl*+*Enter*. The poster-boxes support Markdown and LaTeX redering *(see below)* and file uploads *(see below)*.

## Using Markdown
<!-- ![Basic](documentation/img/markdown_basic.png) -->
<!-- ![Extended](documentation/img/markdown_extended.png) -->

Using [this Cheatsheet](https://www.markdownguide.org/cheat-sheet/) as a comparison.
Markdown features that are not supported (yet):
- Footnote
- Heading ID
- Definition List
- Emoji
- Highlight
- Subscript
- Superscript

## Using LaTeX
TBD

To align your LaTeX content, use ```$$ your content $$``` for display mode and ```\\( your content \\)``` for inline mode.

## Upload
To upload, hover over the Box you want add content to and click on the <img src="/img/icons/Icons8_flat_opened_folder.svg" width="25" style="position: relative; top: 7px;"/>-Button. Now select your file. Now the content of your file should be inside a placeholder, you can click outside of your selected Box.

Now, the text with the placeholder should disappear and the content should be correctly redered.
If not please check if the file content has the correct formating.

### Image-Upload
The user can upload several types of content into a box. For images the supported file types are:
- ```png```
- ```jpg```
- ```gif```

Once uploaded, the Box will automatically display a placeholder,
something like: ```![alt text](name.png)```. You can arrange this line in your Text Box however you like.
To remove the image, simply delete the placeholder line. All images will be stored in cache. To view them, navigate to the Projects-page manage them.
To restore your removed image, include the before mentioned placeholderline. Make sure you are using the correct file name of your image.

### Chart-Upload
Additionally the user can upload charts of the following file types:
- ```csv``` (for rendering simple charts)
After uploading the file it should look something like this:
```console
```plotly-scatter
x,y
1,2
3,2
3,6
0,3
-2,4
3,3
```
```
Valid chart types are: ```scatter```, ```line```, ```bar```, ```pie```
To modify the chart type, replace the chart type with one of the other types e.g.: ```plotly-bar```

- ```json``` (for rendering more complex charts, high customization potential; see path: ```/plotly/examples/*.json```). All example charts are from the [Official Plotly documentation](https://plotly.com/javascript/plotly-fundamentals/) and converted into a workable ```json``` format.

