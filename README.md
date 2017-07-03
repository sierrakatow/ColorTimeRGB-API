**ColorTimeRGB API**
----

* **URL**

  ``/request.php?``

* **Method:**

  `GET`
  
*  **URL Params**
 
   `color1=[R],[G],[B],[P]` OR `colorscheme=[pastel|rainbow|american|fire|grayscale|earth]`

   **Optional:**
 
    `color2=[R],[G],[B],[P]`
    `pmin1=[0-100]`
    `pmax1=[0-100]`
    `pmin2=[0-100]`
    `pmax2=[0-100]`

* **Data Params**

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `{ meta : { count : 200, ... }, data : [...] }`
 
* **Error Response:**

  * **Code:** 422 UNPROCESSABLE ENTRY <br />
    **Content:** `{ error : "Parameter requirements not met." }`
    