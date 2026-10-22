</main>

<footer class="site-footer">
    <div class="container">
        <p>E-Commerce Product Catalog System &copy; <?php echo date('Y'); ?></p>
        <p>BCA 5th Semester &ndash; PHP with MySQL Mini Project (PCA2)</p>
    </div>
</footer>

</body>
</html>
<?php
// Close the database connection (opened in header.php)
if (isset($conn)) {
    mysqli_close($conn);
}