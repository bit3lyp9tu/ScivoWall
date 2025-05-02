import os

import unittest
import mypy
import re
from selenium import webdriver

from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By

# from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.firefox.options import Options

# from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.firefox_binary import FirefoxBinary
from selenium.webdriver.firefox.service import Service as FirefoxService


class PythonOrgSearch(unittest.TestCase):

    address = ""

    def setUp(self):
        options = Options()

        if os.environ.get("GITHUB_ACTIONS"):
            options.add_argument("--headless")

            options.binary_location = "/usr/bin/firefox"
            service = FirefoxService(
                executable_path="/home/runner/cache/.driver/geckodriver"
            )
            self.driver = webdriver.Firefox(service=service, options=options)
            self.address = "127.0.0.1:8080"
        else:
            self.driver = webdriver.Firefox(options=options)
            self.address = "localhost"

    def test_search_in_python_org(self):
        driver = self.driver
        driver.get(f"http://{self.address}/scientific_poster_generator/login.php")
        self.assertIn("Poster Generator", driver.title)
        print("Testing Page: " + driver.title)

        self.login_page(driver)

        self.user_page(driver)
        self.poster_page(driver, "")
        self.admin_user(driver, "")
        self.index_page(driver)

        self.logout(driver)

    def tearDown(self):
        if os.environ.get("GITHUB_ACTIONS"):
            self.driver.close()

    def login_page(self, driver):

        # check both empty
        self.login_fill_form(driver, "", "")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check wrong name
        self.login_fill_form(driver, "Max Mustermann" + "123", "AbC123-98xy")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check wrong pw
        self.login_fill_form(driver, "Max Mustermann", "AbC123-98xy" + "abc")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        self.login_clear_form(self.driver)

        # check right name+pw
        self.login_fill_form(driver, "Max Mustermann", "AbC123-98xy")
        driver.find_element(By.ID, "login").click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/projects.php",
            driver.current_url,
        )

        # check cookie session
        session_id = driver.get_cookie("sessionID")
        self.assertIsNotNone(session_id)

        pass

    def login_fill_form(self, driver, name, pw):
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )
        filed_name = driver.find_element(By.ID, "name")
        filed_name.send_keys(name)
        filed_name.send_keys(Keys.RETURN)

        filed_pw = driver.find_element(By.ID, "pw")
        filed_pw.send_keys(pw)
        filed_pw.send_keys(Keys.RETURN)

    def login_clear_form(self, driver):
        driver.find_element(By.ID, "name").clear()
        driver.find_element(By.ID, "pw").clear()

    def logout(self, driver):
        driver.get(f"http://{self.address}/scientific_poster_generator/projects.php")
        # check if page contains logout
        logout = driver.find_element(By.ID, "logout")
        self.assertIsNotNone(logout)
        logout.click()
        self.assertEqual(
            f"http://{self.address}/scientific_poster_generator/login.php",
            driver.current_url,
        )

        # check if correct logout   ???
        pass

    def register(self, driver, name, pw):
        # check existing name
        # check invalid pw
        # check two diffent pw
        # check correct action + now at login page
        pass

    def user_page(self, driver):
        # check poster list correctly loaded
        # check add new poster
        #   check right date
        # check edit poster title
        # check delete new poster
        # check access poster

        # check author list correctly loaded
        # check edit author name
        # check delete author

        # check image list  ???
        pass

    def poster_page(self, driver, title):
        # check right title
        # check edit title
        #   +globally
        # check empty authors
        # check add author
        # check author list switch order
        # check author stored
        # check add box
        #   +globally
        # check basic edit box
        # check box markdown render
        # check box math render

        # check box plotly render   ???
        # check box image render    ???
        #   + stored globally

        # check visibility
        #   - private
        #   - public

        # check right edit date
        pass

    def admin_user(self, driver, pw):
        # check login successfully
        # check posters set on public
        # check set poster to visible
        pass

    def index_page(self, driver):
        # check poster count
        # check poster#1 content
        # check prevent editing:
        #   -title
        #   -add author
        #   -author item interaction
        #   -change author order
        #   -box text
        #   -box image drop
        #   -box interaction
        pass


if __name__ == "__main__":
    unittest.main()

    # TODO: test todo-to-issue test
    #  labels: enhancement, help wanted
