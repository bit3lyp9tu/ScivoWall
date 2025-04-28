import os

import unittest
import mypy
import re
from selenium import webdriver

# from selenium.webdriver.common.keys import Keys
# from selenium.webdriver.common.by import By
# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options

# from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
from selenium.webdriver.firefox.service import Service as FirefoxService


class PythonOrgSearch(unittest.TestCase):

    address = ""

    def setUp(self):
        options = Options()
        options.add_argument("--headless")

        if os.environ.get("GITHUB_ACTIONS"):
            options.binary_location = "/usr/bin/firefox"
            service = FirefoxService(executable_path="/usr/local/bin/geckodriver")
            self.driver = webdriver.Firefox(service=service, options=options)
            self.address = "127.0.0.1:8080"
        else:
            self.driver = webdriver.Firefox(options=options)
            self.address = "localhost"

    def test_search_in_python_org(self):
        driver = self.driver
        driver.get(f"http://{self.address}/scientific_poster_generator/login.php")
        # print(driver.title)
        self.assertIn("Poster Generator", driver.title)

    def tearDown(self):
        self.driver.close()


if __name__ == "__main__":
    # print("Hello World")

    unittest.main()
