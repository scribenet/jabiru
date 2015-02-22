######
Syntax
######

This is the full ScribleMark_ syntax definition. The latest stable syntax definition can always be found on RTD_.
You may take a brief look at the syntax and quickly believe this to be another simple Markdown_ extension, such a
conclusion would be incorrect. While based loosely on Markdown_ and incorporating elements of `GitHub Flavored Markdown`_
and other extensions, is it important to note that ScibleMark's syntax is *both a sub- and super-set* of the base
Markdown implementation, as well as its extensions.

Generally, this means we have selectively *included and excluded* syntax from Markdown_ and its extensions, as well as
re-used previously implemented conventions differently, to better align the final feature-set of ScribleMark with the
goals of `Scribe Inc.`_. As such, attempting to parse regular Markdown_, GFM_, `Markdown Extra`_, or other well-known
implementations directly via this parser will very likely *not* function as you expect! [#fsyn1]_ !

********************
Block-Level Elements
********************

Block-Level Elements include Headings_, Paragraphs_, `Horizontal Rules`_, `Lists`_, `Quotes`_, `Code Blocks`_,
`Call-Outs`_, `Security Restrictions`_, `Rows and Columns`_, and Tables_.

Headings
========

Only Markdown ATX-Style headings are supported [#fhr1]_ . Heading levels one through six [#fhr2]_ can be defined by prefixing
the heading text with the the hash ``#`` symbol, using the number of hashes to define the heading level.

.. code-block:: none

   # Heading 1
   ## Heading 2
   ### Heading 3
   #### Heading 4
   ##### Heading 5
   ###### Heading 6

Step Headings
-------------

Step heads [#fshr1]_ syntax follows the same ATX-Style headings as defined when using regular `Headings`_.
Headings can optionally be marked as check-able by using the ``[ ]`` or ``[x]`` syntax directly following the hash
symbols. Unlike `Lists`_, the empty square brackets and brackets containing an ``x`` specify if the step is optional or
required, respectively. It is important to note that simple sanity checking does occur: as the below example describes
once a top-level heading has been marked as optional, all sub-headings of that element are, by implication, optional
(thus a required step below an optional step will be ignored and instead parsed as optional).

.. code-block:: none

   # [x] Heading 1 (A Required Step)
   ## [x] Heading 1.1 (Another Required Step)
   ## [ ] Heading 1.2 (An Optional Step)
   # [ ] Heading 2 (An Optional Step)
   ## [x] Heading 2.1 (A Force Optional Step, B/C Of It's Parent)
   # [X] Heading 3 (The ``x`` can be both upper and lowercase)
   ## [x] Heading 3.1 (Required)
   ### [x] Heading 3.1.1 (Required)
   ### [ ] Heading 3.1.2 (Optional)

Lists
=====

Lists...

*********************
Inline-Level Elements
*********************

None yet...

*********
Footnotes
*********

.. [#fsyn1] If you are looking for a Markdown_ parser, with support for common extensions, I would highly advise looking
            into ParseDown_, a speedy and feature-rich parser with support for PHP_ 5.2 through 5.6 as well as hhvm_ .
.. [#fhr1] SetExt, Sphinx, or any other heading syntax is not supported.
.. [#fhr2] Heading levels deeper than six (such as ``<h7>`` via seven hash symbols) are not supported.
.. [#fshr1] `Step Headings`_ were previously known as "Checkbox Headings" or "Check-able Headings".

**********
References
**********

.. target-notes::

.. _Scribe Inc.: https://scribenet.com/
.. _Markdown: http://daringfireball.net/projects/markdown/syntax
.. _GitHub Flavored Markdown: https://help.github.com/articles/github-flavored-markdown/
.. _GFM: https://help.github.com/articles/github-flavored-markdown/
.. _Markdown Extra: https://michelf.ca/projects/php-markdown/extra/
.. _ParseDown: http://parsedown.org/
.. _PHP: http://php.net/
.. _hhvm: http://hhvm.com/
.. _RTD: http://readthedocs.org/
.. _ScribleMark: https://scribe.software/code/projects/PHPL/repos/scriblemark-library
