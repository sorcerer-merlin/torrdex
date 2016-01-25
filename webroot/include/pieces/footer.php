  </td>
  </tr>
<?php
	if ($configOptions_Booleans['show_disclaimer'] == "true") {
?>
  <!-- Start Disclaimer -->
  <thead><tr class="mytable">
    <th scope="col">Disclaimer</th>
  </tr></thead>
  <tr class="mytable">
  	<td class="mytable" style="font-family: Snippet; font-style:italic; font-size:16px;">
    <br />
    <div align="center">   
       Sorcerer Merlin and MediEvil Ages INC. are in <b><u>NO</u></b> way, shape, or form affiliated with the content shown above.  There are <b><u>NO</b></u> torrents, files, or other material hosted on our servers. The public BitTorrent community shares these torrents and their associated files. This application only scrapes databases from other torrent indexes and displays this information for others. <b><u>NO</b></u> laws have been broken. Have a nice day!
    </div>
    <br />
    </td>
  </tr>
  <!-- End Disclaimer -->
<?php
	}
	
	if ($configOptions_Booleans['show_copyright'] == "true") {
?>
  <!-- Copyright info -->
  <thead><tr class="mytable">
    <th scope="col" style="font-family: Snippet; text-align: center; font-size:16px;">Copyright &copy; 2014-2016 by Sorcerer Merlin & MediEvil Ages INC (under <a href="http://www.gnu.org/licenses/gpl-3.0-standalone.html">GNU GPLv3</a>). All rights reserved. Hosted on <a href="https://github.com/sorcerer-merlin/torrdex">GitHub</a>.</th>
  </tr></thead>
  <!-- End Copyright -->
<?php } ?>
 </table>
 <img id="bottom" src="/img/linux-inside.png" height="150" width="166" alt="Linux Inside">
</body>
</html>
