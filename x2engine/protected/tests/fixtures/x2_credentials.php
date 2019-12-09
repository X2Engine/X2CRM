<?php
$custom = __DIR__.'/x2_credentials-local.php'; // The liveDeliveryTest alias should be defined in this file
$customCreds = file_exists($custom) ? require($custom) : array();
return array_merge($customCreds,array(
	'testUser' => array(
		'id' => '1',
		'name' => 'Sales Rep\'s Email Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'EmailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'yimf3vRGlOktLg2f1E424CRIH67Nx3E4STM15MAWtvXDQ7+pBJq1UIQobuOUx5iJXP4vYhCms7owtKRgAUfRqYTq3DFM0t/wOSO6ABKWjSa7uWBL8OpHnp5iEeo04RP+jObCJlCoSUev70TeE7Y3G9RkdU6T28RPURf8/H0jtcWIu42RzVCoEpLwpyuU0LBznYB3BvaMJJtsnDeArGNsuMI/UbnvvSR3Z4Xkkr1YLLCL2j5GKqBp8faruFe5h+rrh/QZnwSB7rdGH9vLwLQm/6onBnamx7clBf5GkYNf9bFWrvF+6nTz7QpBq6WiFeUTlPiEu9iYDfm5NgaMkUwJ9gvV41QISBrqmxIgWnI=',
	),
	'gmail1' => array(
		'id' => '2',
		'name' => 'Sales Rep\'s 1st GMail Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'yimf3vRGlOktLg2f1E425yBXDqTN2HccDmxj5N8EsPfDVb+/CJe5T+ZgKcff14OdPrd1cBikqbps9fEvUAaHqcvryjYNw9S/IS3sFQ6QnHf1/iUJ5P9H29FwBNku4Q6oz+bBPRfmFkfqoFDdJaFnV5NlNhjUwelcREm18nMn+8fJpIHAkgWuDZeizmzGl/Eu3JUgW7OeMJJsnDearHZsuMI/UbnvvSR3Z4Xkkr1YLLCG3ixXGKN+zLrz9R6vjur3h+A=',
	),
	'gmail2' => array(
		'id' => '3',
		'name' => 'Sales Rep\'s 2nd GMail Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'yimf3vRGlOktLg2f1E425yBXDqTN2HccDmxj5N8EsPfDVb+/CJe5T+ZgKcff14OdPrd1cBikqbps9fEvUAaHqcvryjYNw9S/IS3sFQ6QnHf1/iUJ5P9H29FwBNku4Q6oz+bBPRfmFkfqoFDdJaFnV5NlNhjUwelcREm18nMn+8fJpIHAkgWuDZeizmzGl/Eu3JUgW7OeMJJsnDearHZsuMI/UbnvvSR3Z4Xkkr1YLLCG3ixXGKN+zLrz9R6vjur3h+A=',
	),
	'backupUser' => array(
		'id' => '4',
		'name' => 'Sales Rep\'s Backup Email Account',
		'userId' => '2',
		'private' => '1',
		'isEncrypted' => 1,
		'modelClass' => 'GMailAccount',
		'createDate' => NULL,
		'lastUpdated' => NULL,
		'auth' => 'yimf3vRGlOktLg2f1E425yBXDqTN2HccDmxj5N8EsPfDVb+/CJe5T+ZgKcff14OdPrd1cBikqbps9fEvUAaHqcvryjYNw9S/IS3sFQ6QnHf1/iUJ5P9H29FwBNku4Q6oz+bBPRfmFkfqoFDdJaFnV5NlNhjUwelcREm18nMn+8fJpIHAkgWuDZeizmzGl/Eu3JUgW7OeMJJsnDearHZsuMI/UbnvvSR3Z4Xkkr1YLLCG3ixXGKN+zLrz9R6vjur3h+A=',
	),
));
?>
