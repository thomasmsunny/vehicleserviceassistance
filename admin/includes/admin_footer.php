      </main>
    </div>
  </div>

  <!-- Footer -->
  <footer class="text-center py-3 mt-5" style="background-color: #2c3e50; color: white;">
    <p class="mb-0">&copy; <?= date('Y') ?> Vehicle Service Management System. All Rights Reserved.</p>
  </footer>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
  
  <script>
    // Initialize DataTables
    $(document).ready(function() {
      $('.data-table').DataTable({
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "language": {
          "search": "Search:",
          "lengthMenu": "Show _MENU_ entries",
          "info": "Showing _START_ to _END_ of _TOTAL_ entries",
          "paginate": {
            "first": "First",
            "last": "Last",
            "next": "Next",
            "previous": "Previous"
          }
        }
      });

      // Confirmation dialogs for delete actions
      $('.delete-btn').on('click', function(e) {
        if(!confirm('Are you sure you want to delete this item?')) {
          e.preventDefault();
        }
      });

      // Auto-hide alerts after 5 seconds
      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
    });
  </script>

  <!-- Custom page scripts -->
  <?php if(isset($custom_scripts)) echo $custom_scripts; ?>
</body>
</html>
