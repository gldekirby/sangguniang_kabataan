<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Available Reports - Web Desktop Table View with Features
  </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   /* Scrollbar for table container */
   .table-container::-webkit-scrollbar {
     height: 8px;
   }
   .table-container::-webkit-scrollbar-thumb {
     background-color: #2563eb; /* blue-600 */
     border-radius: 4px;
   }
   /* For focus outlines */
   a:focus, button:focus, input:focus, select:focus {
     outline-offset: 2px;
   }
   /* Fix search icon inside input */
   .search-wrapper {
     position: relative;
     width: 100%;
   }
   .search-wrapper input {
     padding-right: 2.5rem;
   }
   .search-wrapper .fa-search {
     position: absolute;
     right: 0.75rem;
     top: 50%;
     transform: translateY(-50%);
     pointer-events: none;
     color: skyblue; /* slate-400 */
   }
   /* Cursor pointer for folder rows */
   tr.folder-row {
     cursor: pointer;
   }
  </style>
 </head>
 <body class="font-inter bg-white ">
   <!-- Desktop Body -->
   <main class="flex-grow p-6">
    <div class="max-w-full mx-auto h-full flex flex-col" style="min-height: 500px;">
     <!-- Controls: Search, Filter, Sort -->
     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div class="search-wrapper sm:w-1/3 w-full">
       <label for="searchInput" class="text-black font-semibold sr-only">Search Reports</label>
       <input aria-label="Search reports by name" autocomplete="off" class="w-full rounded-md border border-slate-700 bg-white px-3 py-2 text-black placeholder-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" id="searchInput" placeholder="Search reports..." type="text"/>
       <i class="fas fa-search"></i>
      </div>
      <div class="flex items-center gap-4 w-full sm:w-auto">
       <label for="filterType" class="text-black font-semibold whitespace-nowrap">
        Filter Type:
       </label>
       <select aria-label="Filter reports by type" class="rounded-md border bg-white px-3 py-2 text-black focus:border-blue-500 focus:ring-1 focus:ring-blue-500" id="filterType">
        <option value="all" selected>
         All
        </option>
        <option value="pdf">
         PDF
        </option>
        <option value="doc">
         DOC
        </option>
        <option value="xls">
         XLS
        </option>
       </select>
      </div>
      <div class="flex items-center gap-4 w-full sm:w-auto">
       <label for="sortSelect" class="text-black font-semibold whitespace-nowrap">
        Sort By:
       </label>
       <select aria-label="Sort reports" class="rounded-md border border-slate-700 bg-white px-3 py-2 text-black focus:border-blue-500 focus:ring-1 focus:ring-blue-500" id="sortSelect">
        <option value="name-asc" selected>
         Name (A-Z)
        </option>
        <option value="name-desc">
         Name (Z-A)
        </option>
        <option value="type-asc">
         Type (A-Z)
        </option>
        <option value="type-desc">
         Type (Z-A)
        </option>
       </select>
      </div>
     </div>
     <!-- Table -->
     <div class="table-container overflow-x-auto flex-grow">
      <table aria-label="Available reports table" class="min-w-full table-auto border-collapse">
       <thead>
        <tr class="bg-slate-800 text-white select-none">
         <th class="border border-slate-700 px-6 py-3 text-left text-lg font-semibold">
          Report
         </th>
         <th class="border border-slate-700 px-6 py-3 text-center text-lg font-semibold w-24">
          Type
         </th>
         <th class="border border-slate-700 px-6 py-3 text-center text-lg font-semibold w-32">
          Open
         </th>
         <th class="border border-slate-700 px-6 py-3 text-center text-lg font-semibold w-32">
          Download
         </th>
        </tr>
       </thead>
       <tbody class="text-black" id="reportsTableBody">
        <!-- Folder rows and report rows will be dynamically inserted here -->
       </tbody>
      </table>
     </div>
    </div>
   </main>
   <script>
    (() => {
      const searchInput = document.getElementById('searchInput');
      const filterType = document.getElementById('filterType');
      const sortSelect = document.getElementById('sortSelect');
      const tableBody = document.getElementById('reportsTableBody');

      // All report data with folder info and details
      const reportsData = [
        {
          folderKey: "abyip_reports",
          folderLabel: "Abyip Reports",
          reports: [
            {
              name: "Abyip Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/9370fd80-ceb8-465b-2c78-768f5d0e9ef0.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Abyip Reports PDF file",
              openHref: "reports/abyip_reports.php",
              downloadHref: "reports/abyip_reports/abyip_reports.php"
            }
          ]
        },
        {
          folderKey: "annual_budget_reports",
          folderLabel: "Annual Budget Reports",
          reports: [
            {
              name: "Annual Budget Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/5d168c26-ca87-4825-15dc-9d1e15bfee2a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Annual Budget Reports PDF file",
              openHref: "reports/annual_budget_reports.php",
              downloadHref: "reports/annual_budget_reports/annual_budget_reports.php"
            }
          ]
        },
        {
          folderKey: "funds_reports",
          folderLabel: "Funds Reports",
          reports: [
            {
              name: "Funds Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Funds Reports PDF file",
              openHref: "reports/funds_report.php",
              downloadHref: "reports/funds_reports/funds_report.php"
            }
          ]
        },
        {
          folderKey: "members_reports",
          folderLabel: "Members Reports",
          reports: [
            {
              name: "Members Purok 1, 2, 2A Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 1, 2, 2A Reports PDF file",
              openHref: "reports/member_list_1,2,2A_report.php",
              downloadHref: "reports/members_reports/member_list_1,2,2A_report.php"
            },
            {
              name: "Members Purok 3, 4, 6 Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 3, 4, 6 Reports PDF file",
              openHref: "reports/member_list_3,4,6_report.php",
              downloadHref: "reports/members_reports/member_list_3,4,6_report.php"
            },
            {
              name: "Members Purok 3A, 12, 14 Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 3A, 12, 14 Reports PDF file",
              openHref: "reports/member_list_3A,12,14_report.php",
              downloadHref: "reports/members_reports/member_list_3A,12,14_report.php"
            },
            {
              name: "Members Purok 11A, 11C, 11D Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 11A, 11C, 11D Reports PDF file",
              openHref: "reports/member_list_11A,11C,11D_report.php",
              downloadHref: "reports/members_reports/member_list_11A,11C,11D_report.php"
            },
            {
              name: "Members Purok 5, 7, 13, 9 Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 5, 7, 13, 9 Reports PDF file",
              openHref: "reports/member_list_5,7,13,9_report.php",
              downloadHref: "reports/members_reports/member_list_5,7,13,9_report.php"
            },
            {
              name: "Members Purok 11, 11B, 10A Reports",
              type: "pdf",
              iconSrc: "https://storage.googleapis.com/a1aa/image/d68dc967-8cc2-4051-4f5b-a72bbcd2d15a.jpg",
              iconAlt: "Red PDF icon with white letters PDF representing Members Purok 11, 11B, 10A Reports PDF file",
              openHref: "reports/member_list_11,11B,10A_report.php",
              downloadHref: "reports/members_reports/member_list_11,11B,10A_report.php"
            }
          ]
        }
      ];

      // State to track which folders are open (true) or closed (false)
      const folderOpenState = {};

      // Initialize all folders as open by default
      reportsData.forEach(folder => {
        folderOpenState[folder.folderKey] = true;
      });

      // Create icon elements for folder open/close
      function createFolderIcon(isOpen) {
        const icon = document.createElement('i');
        icon.className = isOpen ? 'fas fa-folder-open text-yellow-500 text-xl' : 'fas fa-folder text-yellow-500 text-xl';
        icon.setAttribute('aria-hidden', 'true');
        return icon;
      }

      // Create icon element for file type
      function createFileTypeIcon(type) {
        const icon = document.createElement('i');
        icon.className = 'fas ';
        switch(type) {
          case 'pdf':
            icon.className += 'fa-file-pdf text-red-500 text-xl';
            icon.title = 'PDF file';
            break;
          case 'doc':
            icon.className += 'fa-file-word text-blue-600 text-xl';
            icon.title = 'DOC file';
            break;
          case 'xls':
            icon.className += 'fa-file-excel text-green-600 text-xl';
            icon.title = 'XLS file';
            break;
          default:
            icon.className += 'fa-file text-gray-500 text-xl';
            icon.title = 'File';
        }
        return icon;
      }

      // Render the table rows based on current filter, sort, and folder open state
      function renderTable() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const filterVal = filterType.value;
        const sortVal = sortSelect.value;

        // Clear table body
        tableBody.innerHTML = '';

        // Filter and sort reports inside each folder
        reportsData.forEach(folder => {
          // Filter reports by search and type
          let filteredReports = folder.reports.filter(report => {
            const name = report.name.toLowerCase();
            const type = report.type.toLowerCase();
            const matchesSearch = name.includes(searchTerm);
            const matchesFilter = filterVal === 'all' || filterVal === type;
            return matchesSearch && matchesFilter;
          });

          // Sort filtered reports
          filteredReports.sort((a, b) => {
            const nameA = a.name.toLowerCase();
            const nameB = b.name.toLowerCase();
            const typeA = a.type.toLowerCase();
            const typeB = b.type.toLowerCase();

            switch(sortVal) {
              case 'name-asc':
                return nameA.localeCompare(nameB);
              case 'name-desc':
                return nameB.localeCompare(nameA);
              case 'type-asc':
                return typeA.localeCompare(typeB);
              case 'type-desc':
                return typeB.localeCompare(typeA);
              default:
                return 0;
            }
          });

          // If no reports after filtering, skip folder
          if(filteredReports.length === 0) return;

          // Folder header row
          const folderRow = document.createElement('tr');
          folderRow.className = 'bg-slate-100 font-semibold text-slate-900 select-none folder-row';
          folderRow.setAttribute('aria-label', `Folder ${folder.folderLabel}`);
          folderRow.setAttribute('tabindex', '0');
          folderRow.dataset.folderKey = folder.folderKey;

          // Folder cell with icon and label
          const folderCell = document.createElement('td');
          folderCell.colSpan = 4;
          folderCell.className = ' px-6 py-2 flex items-center gap-2';

          const folderIcon = createFolderIcon(folderOpenState[folder.folderKey]);
          folderCell.appendChild(folderIcon);

          const folderLabelSpan = document.createElement('span');
          folderLabelSpan.textContent = folder.folderLabel;
          folderCell.appendChild(folderLabelSpan);

          folderRow.appendChild(folderCell);
          tableBody.appendChild(folderRow);

          // If folder is open, render its reports
          if(folderOpenState[folder.folderKey]) {
            filteredReports.forEach(report => {
              const reportRow = document.createElement('tr');
              reportRow.className = 'hover:bg-blue-500 transition-colors cursor-pointer';
              reportRow.dataset.name = report.name;
              reportRow.dataset.type = report.type;

              // Report name cell with icon and text
              const nameCell = document.createElement('td');
              nameCell.className = ' px-6 py-4 flex items-center gap-4 pl-12 truncate';

              const img = document.createElement('img');
              img.src = report.iconSrc;
              img.alt = report.iconAlt;
              img.className = 'w-10 h-10 object-contain';
              img.width = 40;
              img.height = 40;
              nameCell.appendChild(img);

              const span = document.createElement('span');
              span.className = 'truncate';
              span.textContent = report.name;
              nameCell.appendChild(span);

              reportRow.appendChild(nameCell);

              // Type cell with icon
              const typeCell = document.createElement('td');
              typeCell.className = 'px-6 py-4 text-center font-medium';
              const typeIcon = createFileTypeIcon(report.type);
              typeCell.appendChild(typeIcon);
              reportRow.appendChild(typeCell);

              // Open cell with link
              const openCell = document.createElement('td');
              openCell.className = 'px-6 py-4 text-center';
              const openLink = document.createElement('a');
              openLink.href = report.openHref;
              openLink.target = '_blank';
              openLink.title = `Open ${report.name}`;
              openLink.setAttribute('aria-label', `Open ${report.name}`);
              openLink.className = 'inline-block text-blue-400 hover:text-blue-600 transition-colors';
              const openIcon = document.createElement('i');
              openIcon.className = 'fas fa-external-link-alt text-lg';
              openLink.appendChild(openIcon);
              openCell.appendChild(openLink);
              reportRow.appendChild(openCell);

              // Download cell with link
              const downloadCell = document.createElement('td');
              downloadCell.className = 'px-6 py-4 text-center';
              const downloadLink = document.createElement('a');
              downloadLink.href = report.downloadHref;
              downloadLink.download = '';
              downloadLink.title = `Download ${report.name}`;
              downloadLink.setAttribute('aria-label', `Download ${report.name}`);
              downloadLink.className = 'inline-block text-green-400 hover:text-green-600 transition-colors';
              const downloadIcon = document.createElement('i');
              downloadIcon.className = 'fas fa-download text-lg';
              downloadLink.appendChild(downloadIcon);
              downloadCell.appendChild(downloadLink);
              reportRow.appendChild(downloadCell);

              tableBody.appendChild(reportRow);
            });
          }
        });
      }

      // Toggle folder open/close state and re-render
      function toggleFolder(folderKey) {
        folderOpenState[folderKey] = !folderOpenState[folderKey];
        renderTable();
      }

      // Event delegation for folder row clicks and keyboard interaction
      tableBody.addEventListener('click', e => {
        const tr = e.target.closest('tr.folder-row');
        if(tr) {
          const folderKey = tr.dataset.folderKey;
          if(folderKey) {
            toggleFolder(folderKey);
          }
        }
      });

      tableBody.addEventListener('keydown', e => {
        if(e.key === 'Enter' || e.key === ' ') {
          const tr = e.target.closest('tr.folder-row');
          if(tr) {
            e.preventDefault();
            const folderKey = tr.dataset.folderKey;
            if(folderKey) {
              toggleFolder(folderKey);
            }
          }
        }
      });

      // Event listeners for search, filter, sort
      searchInput.addEventListener('input', renderTable);
      filterType.addEventListener('change', renderTable);
      sortSelect.addEventListener('change', renderTable);

      // Initial render
      renderTable();
    })();
   </script>
 </body>
</html>