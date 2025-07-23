### All4One/Ujallas templating conventions

In our design we use [Atomic Design](http://bradfrost.com/blog/post/atomic-web-design/) and [Scalable and Modular Architecture for css](https://smacss.com/) together.

## The main parts of our twig templates:

 - Base - This defines the main structure of our template
 - Bundles - Any bundle templates
 - Forms - Here we define form widget overrides as usual, and also the forms/form segments.
 - Fregments - Here we have the reusable template snippets, which have some hardcoded data related
 - Layouts - Layouts are block of the page like header, footer, navigation, sidebar, content area
 - Modules - Modules are reusable parts of macros. We divide them into 3 categories:
    - atoms - Smallest type of modules. Like a text, button, alert etc..
    - molecules - Molecules consists of atoms, which makes a new bigger element in our desing to be used. like: button groups, navigation, 
    - organisms - Organisms constiss of atoms and/or molecules. like: multiple types of headers, navigations etc.
    NOTE: In the modules we try to define only macros and try to design them to be reusable. So when I need some concrete data to be used, we use fregments to make them reusable.
    NOTE: In terms of modules, we should try to use the same structure and names as we do in our sass modules.
 - Pages - Pages we call from the controllers. We try to use the same folder structure as in our controllers(routes)
 - Utils - Utility twig files, whihc might define some common config data or favicons, meta data etc... This is rearly used.