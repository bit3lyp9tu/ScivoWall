# User Manual

# Index-Page

To have a poster appear on the **Index-Page** two conditions must be satisfied:

1. **View Mode** - the posters privacy level has to be changed from `private` to `public`.
   *Can be done by an admin **or** by the posters owner.*
2. **Visibility** - the checkbox **Visible = true** must be ticked.
   *Only an admin can set this flag.*

# Projects-Page

| Table   | Purpose |
|---------|--------|
| **Posters** | Core information about each poster |
| **Authors** | Individual author records |
| **Images**  | Uploaded image files |

## Creating a new poster

![](/documentation/img/projects_new.png)

1. Fill in the **Title** field.
2. Click the **Create New Project** button <img src="/documentation/img/create_new_project.png" width="85" style="position:relative; top:4px;">

> **Note** - By default the title you type is turned into a Markdown heading by prefixing it with '# '.

*When an author or an image is added on the **Poster-Page**, the corresponding rows are automatically inserted into the **Authors** and **Images** tables. Those tables can be used later for bulk-maintenance (e.g., deleting unused data).*

The Admin hase some additional privileges to manage all existing Posters.

## Admin-User
For the public poster to be depicted on the index-page the admin is requred to set the poster to visible (tick the checkbox).

![](/documentation/img/poster_visibility_small.png)

An admin can:

* See **all** posters, regardless of their privacy setting.
* Change any poster (title, authors, images, visibility, …).
* Filter the poster list (limit number of rows, search by keyword, etc.) using the filter menu:

![](/documentation/img/poster_filter_menu.png)

# Poster Page

A poster consists of the following elements:

| Element | Description |
|---------|-------------|
| **Title** | The posters name. Editable; supports **Markdown** and **LaTeX** (see sections below). |
| **Authors** | Add an author by typing a name and pressing **Enter**. Existing authors appear in the autocomplete list. To delete an author, hover over the author element and click the delete icon: <img src="/img/icons/Icons8_flat_delete_generic.svg" width="25" style="position:relative; top:10px;">. Authors can be reordered via drag-and-drop. |
| **view_mode** | `private` *(default)* or `public`. A *private* poster never appears on the Index Page. |
| **Boxes (text fields)** | Containers for the body content. Each box can be **selected** (editable) or **unselected** (rendered). In the unselected state the content is displayed as Markdown / LaTeX / images / charts. |
| **Upload area** (in Box) | Allows you to attach images or chart files to a box (see “Upload”). |

## Working with Boxes

* **Select / edit** - Click a box to switch it to *selected* mode.
* **Add a new box** - Click the **Add Box** button <img src="/documentation/img/add_box.png" width="55" style="position:relative; top:5px;">
* **Delete a box** - Remove all text from the box **and** click outside the field.
* **Save changes/Escape Box** - Click elsewhere, press **Ctrl+Enter**, or press **Esc**.
* **Supported rendering** - Markdown & LaTeX (see sections below).
* **File uploads** - Use the folder icon (see “Upload”).

## Using Markdown
<!-- ![Basic](documentation/img/markdown_basic.png) -->
<!-- ![Extended](documentation/img/markdown_extended.png) -->

For a quick reference see the [Markdown Cheat Sheet](https://www.markdownguide.org/cheat-sheet/).

**supported features:**

| Feature |
|---------|
| Heading |
| Bold |
| Italic |
| Blockquote |
| Ordered List |
| Unordered List |
| Code |
| Horizonal Rule |
| Link |
| Image |
| Table |
| Fenced Code Block |
| Strikethrough |
| Task List |

## Using LaTeX

In the meantime you can embed LaTeX as follows:

* **Display mode** - `$$ your-content $$`
* **Inline mode** - `\\( your-content \\)`

> Note: To prevent conflict with Markdown Rendering, avoid using line breaks in your LaTeX.

## Upload

### General upload workflow

1. Hover over the **Box** you want to add a file to.
2. Click the **folder** icon <img src="/img/icons/Icons8_flat_opened_folder.svg" width="25" style="position:relative; top:7px;">
3. Choose a file from the file-picker.
4. The uploaded file is inserted as a **Markdown placeholder** inside the box.
5. Click outside the box - the placeholder disappears and the content is rendered.

If the rendering fails, verify that the file content follows the required format.

### Image upload

Supported image formats:

| Extension |
|-----------|
| `png` |
| `jpg` |
| `jpeg` |
| `gif` |
| `svg` |

After a successful upload the box shows a **Markdown Image Element** such as:

```
![file not found](name.png)
```

You can move this line anywhere inside the box text.

* To **remove** the image, simply delete the Markdown Image Element line.
* All uploaded images are stored in the **cache**; you can manage them on the **Projects-Page**.
* To **restore** a removed image, re-insert the Markdown Image Element with the correct file name.

### Chart upload

Two chart file types are recognised:

| Type | Use case |
|------|----------|
| `csv` | Quick rendering of simple charts |
| `json` | Complex, fullycustomisable charts (see examples [here](https://github.com/bit3lyp9tu/scientific_poster_generator/tree/main/plotly/examples)) |

#### CSV chart example

```csv
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
```

*Valid chart types*: `scatter`, `line`, `bar`, `pie`.
To change the chart type, replace the word after `plotly-` (e.g., `plotly-bar`).

#### JSON chart example

Upload a JSON file that follows Plotlys schema. All example files are taken from the [official Plotly documentation](https://plotly.com/javascript/plotly-fundamentals/) and have been converted to a workable format.

