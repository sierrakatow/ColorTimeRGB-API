**ColorTimeRGB API**
----

* **URL:**

  ``/request.php?``

* **Method:**

  `GET`
  
*  **URL Params:**
 
   `color1=[R],[G],[B],[P]` OR `colorscheme=[pastel|rainbow|american|fire|grayscale|earth]`

   **Optional:**
 
    `color2=[R],[G],[B],[P]`
    `pmin1=[0-100]`
    `pmax1=[0-100]`
    `pmin2=[0-100]`
    `pmax2=[0-100]`
    `pattern=[dotted|stripes]`

* **Data Params**

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `{ meta : { count : 200, ... }, data : [...] }`
 
* **Error Response:**

  * **Code:** 422 UNPROCESSABLE ENTRY <br />
    **Content:** `{ error : "Parameter requirements not met." }`
    
* **Making Changes to the API:**

  * `git status` - see files that have been changed since last commit
  * `git add .` - add all files that've been changed in the current folder
  * `git add [filename]` - add specific file that's been changed
  * `git reset [filename]` - undo a git add
  * `git commit -m "SOME MESSAGE HERE"` - commit added files, staged for upload
  * `git push origin master` - push changes to github
  * `eb deploy --staged` - push changes lives to AWS

* **Useful MySQL Queries:**

  * `DELETE FROM items WHERE category_id = '[SOME CATEGORY]'` - deletes all rows in items that fit that category (corresponding rows in items_colors and items\_colors2 are also deleted)
  * `TRUNCATE TABLE [tablename]` - completely empty and reset a table