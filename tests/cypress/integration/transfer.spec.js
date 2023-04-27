/// <reference types="cypress" />

const packages = [
  "database",
  "dropins",
  "mu-plugins",
  "plugins",
  "themes",
  "uploads",
  "root",
];

describe("Transfer", function () {
  it("Generates Packages & Sends Manifest", function () {
    cy.intercept(
      {
        method: "GET",
        url: `**${encodeURIComponent(
          "/bluehost-site-migrator/v1/migration-package"
        )}*`,
      },
      {
        fixture: "migrationPackages",
        delay: 500,
      }
    ).as("fetchPackages");

    cy.intercept(
      {
        method: "GET",
        url: `**${encodeURIComponent(
          "/bluehost-site-migrator/v1/migration-package"
        )}*%2Fis-valid*`,
      },
      {
        body: false,
        delay: 500,
      }
    ).as("isValid");

    packages.forEach(function (packageName) {
      cy.intercept(
        {
          method: "GET",
          url: `**${encodeURIComponent(
            "/bluehost-site-migrator/v1/migration-package"
          )}*%2Fis-valid*`,
        },
        {
          body: true,
          delay: 500,
        }
      ).as(packageName);

      cy.intercept(
        {
          method: "GET",
          url: `**${encodeURIComponent(
            "/bluehost-site-migrator/v1/migration-package"
          )}*%2Fis-scheduled*`,
        },
        {
          body: true,
          delay: 500,
        }
      ).as(packageName);
    });

    cy.intercept(
      {
        method: "POST",
        url: `**${encodeURIComponent(
          "/bluehost-site-migrator/v1/manifest/send"
        )}*`,
      },
      {
        fixture: "manifestSend",
      }
    ).as("sendManifest");

    cy.intercept(
      {
        method: "GET",
        url: `**${encodeURIComponent(
          "/bluehost-site-migrator/v1/migration-regions"
        )}*`,
      },
      {
        fixture: "migrationRegions",
      }
    ).as("migrationRegions");

    cy.navigateTo("/transfer");

    packages.forEach(function (packageName) {
      cy.contains("p", `Packaging ${packageName}...`);
    });

    cy.wait("@sendManifest");
    cy.hash().should("eq", "#/complete");
  });
});
