food-pantry-ims
===============

Food pantry inventory management system from CS4400. I don't think this is the best way to manage your food pantry, since it was written to spec for CS4400. 

When I first read the requirements and project outline, I wanted to avoid writting superfluous SELECT and UPDATE SQL queries (especially with the column names provided). For each table, Model is extended and given an outline of columns, preferred column aliases (seriously, who's gonna write Pick_Up_Transaction_ID), and in turn handles whether to insert or update, which columns to update (hint, the dirty ones), and what other Models to pull in as relationships. 

To keep from URIs being littered with extensions, requests are rewritten to web/index.php and doled out to the appropriate Controller. 

It's got just enough magic for this project to maintain my sanity.


### Overview (from course document)

The purpose of this project is to analyze, specify, design, implement, document, and demonstrate a database management information system called Food Pantry Information Management (FPIM). The project will proceed in three phases as outlined in the Classical Methodology for Database Development: Analysis & Specification, Design, and Implementation & Testing. The system should be implemented using a Database Management System (DBMS) that supports standard SQL queries. Class administrators will provide you with information about how to access a college-managed MySQL server in order to implement your database. Your professor must approve alternative implementations. Under no circumstances may you use a tool that automatically generates SQL or automatically maps programming objects into the database.
