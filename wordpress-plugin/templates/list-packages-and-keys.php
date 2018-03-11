<?php
$oddEven = 0;
?>
<h1>Package List</h1>
<table>
    <thead>
    <tr>
        <th>Plugin</th>
        <th>KeyFingerPrint</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
        <?php
        /**
         * @var SignedAutoUpdate_PackageInfo $packageInfo
         */
        foreach($this->packageList as $packageInfo) : ?>
        <tr class="<?php $oddEven++%2==0?'odd':'even';?>">
            <td><?=$packageInfo->getPluginSlug();?></td>
            <td><?=$packageInfo->getFingerPrint();?></td>
            <td>
                <a href="<?php echo admin_url('admin.php?page=signed-autoupdate&amp;revoke=' . $packageInfo->getPluginSlug()); ?>">delete</a>
                <a onclick="var k = prompt('please enter the key','some key');if(!k) return false; this.setAttribute('href', this.getAttribute('href')+'&amp;new='+k);" href="<?php echo admin_url('admin.php?page=signed-autoupdate&amp;edit=' . $packageInfo->getPluginSlug()); ?>">edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
